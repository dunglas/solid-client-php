<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle;

use Dunglas\PhpSolidClient\Bundle\Security\SolidAuthenticator;
use Dunglas\PhpSolidClient\SolidClient;
use Dunglas\PhpSolidClient\SolidClientFactory as BaseSolidClientFactory;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
