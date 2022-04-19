<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle;

use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\SolidClient;
use Dunglas\PhpSolidClient\SolidClientFactory as BaseSolidClientFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidClientFactory
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly BaseSolidClientFactory $baseSolidClientFactory,
    ) {
    }

    public function create(): SolidClient
    {
        $oidcClient = null;
        $token = $this->tokenStorage->getToken();
        if ($token && $token->hasAttribute(SolidAuthenticator::OIDC_CLIENT_KEY)) {
            $oidcClient = $token->getAttribute(SolidAuthenticator::OIDC_CLIENT_KEY);
        }

        return $this->baseSolidClientFactory->create($oidcClient);
    }
}
