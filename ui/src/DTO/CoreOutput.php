<?php

declare(strict_types=1);

namespace App\DTO;

class CoreOutput
{
    public function __construct(public int $exitCode, public array $lines) {}
}
