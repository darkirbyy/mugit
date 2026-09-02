<?php

declare(strict_types=1);

namespace App\DTO;

class UserListData
{
    public function __construct(public ?array $userInfoDataList = null) {}
}
