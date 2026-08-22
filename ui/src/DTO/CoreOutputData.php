<?php

declare(strict_types=1);

namespace App\DTO;

class CoreOutputData
{
    public function __construct(public int $exitCode, public array $lineList) {}
}
