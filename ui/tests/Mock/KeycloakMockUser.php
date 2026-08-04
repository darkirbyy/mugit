<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Mainick\KeycloakClientBundle\Interface\AccessTokenInterface;
use Mainick\KeycloakClientBundle\Interface\ResourceOwnerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class KeycloakMockUser implements UserInterface, ResourceOwnerInterface
{
    public function __construct(private Uuid $uuid, private string $username, private string $avatarPath, private bool $isAdmin) {}

    public function getAccessToken(): ?AccessTokenInterface
    {
        return null;
    }

    public function getId(): string
    {
        return $this->uuid->toString();
    }

    public function getEmail(): ?string
    {
        return null;
    }

    public function getName(): ?string
    {
        return null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getFirstName(): ?string
    {
        return null;
    }

    public function getLastName(): ?string
    {
        return null;
    }

    public function toArray(): array
    {
        return [
            'sub' => $this->uuid->toString(),
            'preferred_username' => $this->username,
            'picture' => $this->avatarPath,
        ];
    }

    public function getRoles(): array
    {
        return $this->isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getIsAdmin(): bool{
        return $this->isAdmin;
    }
}
