<?php

declare(strict_types=1);

namespace App\DTO;

class RepoInfoData
{
    public function __construct(public string $name, public int $size) {}
}
