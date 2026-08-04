<?php

declare(strict_types=1);

namespace App\Tests\Mock;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Uid\UuidV4;

class KeycloakMockUserCreate
{
    public function __construct(
        private Packages $packages,
    ) {}

    public function createUser(bool $isAdmin): KeycloakMockUser
    {
        $number = 1;
        $uuid = UuidV4::fromString('11111111-1111-4111-8111-' . 111111111111 * $number);
        $username = 'user' . $number;
        $avatarPath = $this->packages->getUrl('build/tests/avatar' . $number . '.png');

        return new KeycloakMockUser($uuid, $username, $avatarPath, $isAdmin);
    }
}
