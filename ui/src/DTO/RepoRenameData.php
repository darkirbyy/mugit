<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RepoRenameData
{
    public function __construct(
        public string $oldName,
        #[Assert\NotBlank(message: 'repo.rename.empty')] #[Assert\Regex('/' . CoreInteractInterface::REGEX_NAME . '/', message: 'repo.rename.invalid')] public ?string $newName,
    ) {}
}
