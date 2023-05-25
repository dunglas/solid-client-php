<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Security;

use Dunglas\PhpSolidClient\OidcClient;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidAuthenticator extends AbstractLoginFormAuthenticator
{
    public const OIDC_CLIENT_KEY = 'solid_oidc_client';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate('solid_client_login');
    }

    public function supports(Request $request): bool
    {
        return
            $request->getSession()->has(self::OIDC_CLIENT_KEY)
                && (
                    $request->query->has('code')
                    || $request->query->has('error')
                );
    }

    public function authenticate(Request $request): Passport
    {
        $session = $request->getSession();
        try {
            /**
             * @var OidcClient
             */
            $oidcClient = $request->getSession()->get(self::OIDC_CLIENT_KEY);
            $oidcClient->authenticate();
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Cannot finish the Solid OIDC login process.', [], (int) $e->getCode(), $e);
        }

        $rawIdToken = $oidcClient->getIdToken();
        $payload = $oidcClient->decodeToken($rawIdToken)->getPayload();
        if (null === $payload) {
            throw new CustomUserMessageAccountStatusException('Invalid JWT: missing "webid" or "sub" claim');
        }

        $idToken = json_decode($payload, true, 512, \JSON_THROW_ON_ERROR);
        if (!($idToken['webid'] ?? $idToken['sub'] ?? false)) {
            throw new CustomUserMessageAccountStatusException('Invalid JWT: missing "webid" or "sub" claim');
        }

        $passport = new SelfValidatingPassport(new UserBadge($idToken['webid'] ?? $idToken['sub']));
        $passport->setAttribute(self::OIDC_CLIENT_KEY, $oidcClient);

        $session->remove(self::OIDC_CLIENT_KEY);

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        $token = parent::createToken($passport, $firewallName);
        $token->setAttributes($passport->getAttributes());

        return $token;
    }
}
