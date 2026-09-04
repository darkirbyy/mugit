<?php

declare(strict_types=1);

namespace App\DTO;

class LogInfoData
{
    public function __construct(public \DateTime $date, public string $uuid, public string $command) {}
}
