<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class UserKeysRemoveData
{
    public function __construct(
        #[Assert\NotBlank(message: 'user.keys.remove.failed')] #[Assert\Regex('/' . Core::REGEX_KEY . '/', message: 'user.keys.remove.failed')] public string $key,
        #[Assert\NotBlank(message: 'user.keys.remove.failed')] #[Assert\Regex('/' . Core::REGEX_UUID . '/', message: 'user.keys.remove.failed')] public ?string $uuid = null,
    ) {}
}
