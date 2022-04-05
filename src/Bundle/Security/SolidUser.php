<?php

declare(strict_types=1);

namespace Dunglas\PhpSolidClient\Bundle\Security;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Kévin Dunglas <kevin@dunglas.fr>
 */
final class SolidUser implements UserInterface, EquatableInterface
{
    public function __construct(
        private readonly string $identifier,
        private readonly array $roles = [],
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function isEqualTo(UserInterface $user): bool
    {
        return $user->getUserIdentifier() === $this->getUserIdentifier();
    }

    public function eraseCredentials()
    {
    }
}
