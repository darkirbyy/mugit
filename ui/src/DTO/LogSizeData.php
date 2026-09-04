<?php

declare(strict_types=1);

namespace App\DTO;

class LogSizeData
{
    public function __construct(public ?int $size = null) {}
}
