<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient;

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Core\JWKSet;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\Algorithm\ES384;
use Jose\Component\Signature\Algorithm\ES512;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\HS384;
use Jose\Component\Signature\Algorithm\HS512;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS384;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class OidcClient extends OpenIDConnectClient
{
    private ?JWK $dpopPrivateKey = null;

    public function authenticate(): bool
    {
        $this->setCodeChallengeMethod('S256');
        $this->addScope('openid');
        $this->addScope('webid');
        $this->addScope('offline_access');

        return parent::authenticate();
    }

    public function verifyJWTsignature($jwt): bool
    {
        $this->decodeToken($jwt);

        return true;
    }

    public function requestTokens($code, $headers = [])
    {
        $headers[] = 'DPoP: '.$this->createDPoP('POST', $this->getProviderConfigValue('token_endpoint'), false);
        
        return parent::requestTokens($code, $headers);
    }

    // https://base64.guru/developers/php/examples/base64url
    private function base64urlEncode(string $data): string
    {
        // First of all you should encode $data to Base64 string
        $b64 = base64_encode($data);

        // Convert Base64 to Base64URL by replacing “+” with “-” and “/” with “_”
        $url = strtr($b64, '+/', '-_');

        // Remove padding character from the end of line and return the Base64URL result
        return rtrim($url, '=');
    }

    public function createDPoP(string $method, string $url, bool $includeAth = true): string
    {
        if (null === $this->dpopPrivateKey) {
            $this->dpopPrivateKey = JWKFactory::createECKey('P-256', ['use' => 'sig', 'kid' => base64_encode(random_bytes(20))]);
        }

        $jwsBuilder = new JWSBuilder(new AlgorithmManager([new ES256()]));

        $arrayPayload = [
            'htu' => strtok($url, '?'),
            'htm' => $method,
            'jti' => base64_encode(random_bytes(20)),
            'iat' => time(),
        ];
        if ($includeAth) {
            $arrayPayload['ath'] = $this->base64urlEncode(hash('sha256', $this->getAccessToken()));
        }
        $payload = json_encode($arrayPayload, JSON_THROW_ON_ERROR);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature(
                $this->dpopPrivateKey,
                [
                    'alg' => 'ES256',
                    'typ' => 'dpop+jwt',
                    'jwk' => $this->dpopPrivateKey->toPublic()->jsonSerialize(),
                ]
            )
            ->build();

        return (new CompactSerializer())->serialize($jws, 0);
    }

    public function decodeToken(string $jwt): JWS
    {
        try {
            $jwks = JWKSet::createFromJson($this->fetchURL($this->getProviderConfigValue('jwks_uri')));
        } catch (\Exception $e) {
            throw new OpenIDConnectClientException('Invalid JWKS: '.$e->getMessage(), $e->getCode(), $e);
        }

        $headerCheckerManager = new HeaderCheckerManager(
            [new AlgorithmChecker(['RS256', 'RS384', 'R512', 'HS256', 'HS384', 'HS512', 'ES256', 'ES384', 'ES512'])], // TODO: read this from the provider config
            [new JWSTokenSupport()],
        );

        $algorithmManager = new AlgorithmManager([
            new RS256(),
            new RS384(),
            new RS512(),
            new HS256(),
            new HS384(),
            new HS512(),
            new ES256(),
            new ES384(),
            new ES512(),
        ]);

        $serializerManager = new JWSSerializerManager([new CompactSerializer()]);
        $jws = $serializerManager->unserialize($jwt);

        try {
            $headerCheckerManager->check($jws, 0);
        } catch (\Exception $e) {
            throw new OpenIDConnectClientException('Invalid JWT header: '.$e->getMessage(), $e->getCode(), $e);
        }

        $jwsVerifier = new JWSVerifier($algorithmManager);
        if (!$jwsVerifier->verifyWithKeySet($jws, $jwks, 0)) {
            throw new OpenIDConnectClientException('Invalid JWT signature.');
        }

        return $jws;
    }

    protected function getProviderConfigValue($param, $default = null)
    {
        // Hack for compatibility with Solid Node Server
        if ($param === 'code_challenge_methods_supported') {
            return $default ?? ['S256'];
        }

        return parent::getProviderConfigValue($param, $default); // TODO: Change the autogenerated stub
    }
}
