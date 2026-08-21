<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class KeycloakMockUserProvider implements UserProviderInterface
{
    public function __construct() {}

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new \RuntimeException('Unreachable code');
    }

    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    #[\Override]
    public function supportsClass(string $class): bool
    {
        return KeycloakMockUser::class == $class;
    }
}
