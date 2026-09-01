<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserKeysAddData
{
    public function __construct(
        #[Assert\NotBlank(message: 'user.keys.add.empty')] #[Assert\Regex('/' . CoreInteractInterface::REGEX_FULL_KEY . '/', message: 'user.keys.add.invalid'),]
        public ?string $fullKey = null,
        public ?string $uuid = null,
    ) {}
}
