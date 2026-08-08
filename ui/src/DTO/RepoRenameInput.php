<?php

declare(strict_types=1);

namespace App\DTO;

class RepoRenameInput
{
    public function __construct(public string $oldName, public string $newName) {}
}
