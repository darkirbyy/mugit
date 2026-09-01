<?php

declare(strict_types=1);

namespace App\DTO;

class UserKeysInfoData
{
    public function __construct(public string $key, public \DateTime $dateAdded, public ?string $comment) {}
}
