<?php

declare(strict_types=1);

namespace App\DTO;

class CoreErrorData
{
    public function __construct(public string $textKey) {}
}
