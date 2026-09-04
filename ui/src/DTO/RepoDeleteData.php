<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class RepoDeleteData
{
    public function __construct(
        #[Assert\NotBlank(message: 'repo.delete.failed')] #[Assert\Regex('/' . Core::REGEX_NAME . '/', message: 'repo.delete.failed')] public string $name,
    ) {}
}
