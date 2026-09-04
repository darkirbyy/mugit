<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class UserDeleteData
{
    public function __construct(
        #[Assert\NotBlank(message: 'user.delete.failed')] #[Assert\Regex('/' . Core::REGEX_UUID . '/', message: 'user.delete.failed')] public string $uuid,
    ) {}
}
