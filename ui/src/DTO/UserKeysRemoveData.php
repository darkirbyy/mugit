<?php

declare(strict_types=1);

namespace App\DTO;

class UserKeysRemoveData
{
    public function __construct(public string $key, public ?string $uuid = null) {}
}
