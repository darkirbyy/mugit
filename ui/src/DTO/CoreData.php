<?php

declare(strict_types=1);

namespace App\DTO;

class CoreData
{
    public function __construct(public int $exitCode, public array $lineList) {}
}
