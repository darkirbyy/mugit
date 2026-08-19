<?php

declare(strict_types=1);

namespace App\DTO;

class FlashMessage
{
    public function __construct(public string $textKey, public bool $canRetry = false, public bool $canClose = true) {}
}
