<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class KeycloakMockUser implements UserInterface
{
    public function __construct(private Uuid $uuid, private string $username, private string $avatarPath, private bool $isAdmin) {}

    public function getId(): string
    {
        return $this->uuid->toString();
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function eraseCredentials(): void {}
}
