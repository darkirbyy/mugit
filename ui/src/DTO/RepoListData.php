<?php

declare(strict_types=1);

namespace App\DTO;

class RepoListData
{
    public function __construct(public ?array $repoInfoDataList = null) {}
}
