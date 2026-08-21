<?php

declare(strict_types=1);

namespace App\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class TurboframeOnly
{
    public function __construct(public string $redirectRoute) {}
}
