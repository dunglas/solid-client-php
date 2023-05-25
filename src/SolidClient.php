<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient;

use EasyRdf\Graph;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidClient
{
    private const DEFAULT_MIME_TYPE = 'text/turtle';
    private const LDP_BASIC_CONTAINER = 'http://www.w3.org/ns/ldp#BasicContainer';
    private const LDP_RESOURCE = 'http://www.w3.org/ns/ldp#Resource';
    private const OIDC_ISSUER = 'http://www.w3.org/ns/solid/terms#oidcIssuer';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?OidcClient $oidcClient = null,
    ) {
    }

    public function createContainer(string $parentUrl, string $name, string $data = null): ResponseInterface
    {
        return $this->post($parentUrl, $data, $name, true);
    }

    /**
     * Creates a new resource by performing a Solid/LDP POST operation to a specified container.
     *
     * @see https://github.com/solid/solid-web-client/blob/main/src/client.js#L231=
     */
    public function post(string $url, string $data = null, string $slug = null, bool $isContainer = false, array $options = []): ResponseInterface
    {
        if ($isContainer || !isset($options['headers']['Content-Type'])) {
            $options['headers']['Content-Type'] = self::DEFAULT_MIME_TYPE;
        }
        if (null !== $data) {
            $options['body'] = $data;
        }
        if (null !== $slug) {
            $options['headers']['Slug'] = $slug;
        }

        $options['headers']['Link'] = sprintf('<%s>; rel="type"', $isContainer ? self::LDP_BASIC_CONTAINER : self::LDP_RESOURCE);

        return $this->request('POST', $url, $options);
    }

    public function get(string $url, array $options = []): ResponseInterface
    {
        return $this->request('GET', $url, $options);
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        if ($accessToken = $this->oidcClient?->getAccessToken()) {
            $options['headers']['Authorization'] = 'DPoP '.$accessToken;
            $options['headers']['DPoP'] = $this->oidcClient->createDPoP($method, $url, true);
        }

        return $this->httpClient->request($method, $url, $options);
    }

    public function getProfile(string $webId, array $options = []): Graph
    {
        $response = $this->get($webId, $options);
        if (null !== $format = $response->getHeaders()['content-type'][0] ?? null) {
            // strip parameters (such as charset) if any
            $format = explode(';', $format, 2)[0];
        }

        return new Graph($webId, $response->getContent(), $format);
    }

    public function getOidcIssuer(string $webId, array $options = []): string
    {
        $graph = $this->getProfile($webId, $options);

        $issuer = $graph->get($webId, sprintf('<%s>', self::OIDC_ISSUER))?->getUri();
        if (!\is_string($issuer)) {
            throw new Exception('Unable to find the OIDC issuer associated with this WebID', 1);
        }

        return $issuer;
    }
}
