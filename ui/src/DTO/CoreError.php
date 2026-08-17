<?php

declare(strict_types=1);

namespace App\DTO;

class CoreError
{
    public function __construct(public string $messageKey, public array $messageParams = []) {}
}
