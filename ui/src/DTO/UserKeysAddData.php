<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class UserKeysAddData
{
    public function __construct(
        #[Assert\NotBlank(message: 'user.keys.add.empty')] #[Assert\Regex('/' . Core::REGEX_FULL_KEY . '/', message: 'user.keys.add.invalid')] public ?string $fullKey = null,
        #[Assert\NotBlank(message: 'user.keys.add.failed')] #[Assert\Regex('/' . Core::REGEX_UUID . '/', message: 'user.keys.add.failed')] public ?string $uuid = null,
    ) {}
}
