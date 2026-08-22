<?php

declare(strict_types=1);

namespace App\DTO;

class ErrorData
{
    public function __construct(public string $textKey) {}
}
