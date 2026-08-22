<?php

declare(strict_types=1);

namespace App\DTO;

class FormData
{
    public function __construct(public bool $proceed, public array $errorList = []) {}
}
