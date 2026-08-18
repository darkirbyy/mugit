<?php

declare(strict_types=1);

namespace App\DTO;

class RepoListOutput
{
    public function __construct(public array $repoInfoList) {}
}
