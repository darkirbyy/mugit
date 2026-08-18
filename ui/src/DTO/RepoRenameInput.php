<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class RepoRenameInput
{
    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\Regex('/^[a-zA-Z]([a-zA-Z0-9_-])*$/')]
        public string $oldName, 
        #[Assert\NotBlank()]
        #[Assert\Regex('/^[a-zA-Z]([a-zA-Z0-9_-])*$/')]
        public string $newName)
     {}
}
