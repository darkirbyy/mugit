<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoListOutput;
use App\DTO\RepoRenameInput;

interface CoreInteractInterface
{
    const REGEX_NAME = '^[a-zA-Z]([a-zA-Z0-9_-])*$';

    /**
     * List all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically
     *
     * @return CoreError|RepoListOutput
     */
    public function repoList(): CoreError|RepoListOutput;

    /**
     * Create a new repository named $name if not already exists
     *
     * @return CoreError|true
     */
    public function repoCreate(RepoCreateInput $repoCreateInput): CoreError|true;

    /**
     * Rename an existing repository named $oldName to $newName if not already exists
     *
     * @return CoreError|true
     */
    public function repoRename(RepoRenameInput $repoRenameInput): CoreError|true;

    /**
     * Delete an existing repository named $name
     *
     * @return CoreError|true
     */
    public function repoDelete(RepoDeleteInput $repoDeleteInput): CoreError|true;
}
