<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class UserKeysListData
{
    public function __construct(
        #[Assert\NotBlank(message: 'user.keys.list.failed')] #[Assert\Regex('/' . Core::REGEX_UUID . '/', message: 'user.keys.list.failed')] public string $uuid,
        public ?array $userKeysInfoDataList = null,
    ) {}
}
