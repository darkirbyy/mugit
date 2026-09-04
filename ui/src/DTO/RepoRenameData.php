<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface as Core;
use Symfony\Component\Validator\Constraints as Assert;

class RepoRenameData
{
    public function __construct(
        #[Assert\NotBlank(message: 'repo.rename.failed')] #[Assert\Regex('/' . Core::REGEX_NAME . '/', message: 'repo.rename.failed')] public string $oldName,
        #[Assert\NotBlank(message: 'repo.rename.empty')] #[Assert\Regex('/' . Core::REGEX_NAME . '/', message: 'repo.rename.invalid')] public ?string $newName,
    ) {}
}
