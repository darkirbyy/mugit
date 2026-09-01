<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Uid\Uuid;

class UserKeysListData
{
    public function __construct(public Uuid $uuid, public ?array $userKeysInfoDataList = null) {}
}
