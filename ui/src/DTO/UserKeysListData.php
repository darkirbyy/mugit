<?php

declare(strict_types=1);

namespace App\DTO;

class UserKeysListData
{
    public function __construct(public string $uuid, public ?array $userKeysInfoDataList = null) {}
}
