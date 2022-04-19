<?php

/*
 * This file is part of the Solid Client PHP project.
 * (c) Kévin Dunglas <kevin@dunglas.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        // TODO: check if the token hasn't expired
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return SolidUser::class === $class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new SolidUser($identifier);
    }
}
