<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Override;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Uid\UuidV4;

class KeycloakMockUserProvider implements UserProviderInterface
{
    public function __construct(
        private Packages $packages,
        #[Autowire('%mock.keycloak_admin%')] private bool $mockKeycloakAdmin,

    ) {}

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->createUser($this->mockKeycloakAdmin);
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->createUser($this->mockKeycloakAdmin);
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class == KeycloakMockUser::class;
    }

    public function createUser(bool $isAdmin): KeycloakMockUser
    {
        $number = 1;
        $uuid = UuidV4::fromString('11111111-1111-4111-8111-' . 111111111111 * $number);
        $username = 'user' . $number;
        $avatarPath = $this->packages->getUrl('build/tests/avatar' . $number . '.png');

        return new KeycloakMockUser($uuid, $username, $avatarPath, $isAdmin);
    }
}
