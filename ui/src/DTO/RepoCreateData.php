<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class RepoCreateData
{
    public function __construct(
        #[Assert\NotBlank(message: 'repo.create.empty')] #[Assert\Regex('/' . Core::REGEX_NAME . '/', message: 'repo.create.invalid')] public ?string $name,
    ) {}
}
