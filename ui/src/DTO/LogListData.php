<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class LogListData
{
    public function __construct(
        #[Assert\Range(min: 1, max: 10000000000000000, message: 'TODO')] public ?int $offset = null,
        #[Assert\Range(min: 1, max: 100000, message: 'TODO')] public ?int $length = null,
        public ?array $logInfoDataList = null,
    ) {}
}
