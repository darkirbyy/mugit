<?php

declare(strict_types=1);

namespace App\DTO;

class RepoDeleteInput
{
    public function __construct(public string $name) {}
}
