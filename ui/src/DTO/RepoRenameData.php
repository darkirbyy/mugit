<?php

declare(strict_types=1);

namespace App\DTO;

use App\Service\CoreInteractInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RepoRenameData
{
    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\Regex('/' . CoreInteractInterface::REGEX_NAME . '/')]
        public string $oldName,
        #[Assert\NotBlank()]
        #[Assert\Regex('/' . CoreInteractInterface::REGEX_NAME . '/')]
        public ?string $newName,
    ) {}
}
