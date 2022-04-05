<?php

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
        return $class === SolidUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new SolidUser($identifier);
    }
}
