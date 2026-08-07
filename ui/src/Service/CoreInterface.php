<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RepoInfo;

interface CoreInterface
{
    /**
     * Get the list of all existing repositories
     *
     * @return RepoInfo[]
     */
    public function getRepoInfoList(): array;
}
