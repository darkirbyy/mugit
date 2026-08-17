<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoInfo;
use App\DTO\RepoRenameInput;

interface CoreInteractInterface
{
    /**
     * List all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically
     *
     * @return CoreError|RepoInfo[]
     */
    public function repoList(): CoreError|array;

    /**
     * Create a new repository named $name if not already exists
     *
     * @return CoreError|RepoInfo
     */
    public function repoCreate(RepoCreateInput $repoCreateInput): CoreError|RepoInfo;

    /**
     * Rename an existing repository named $oldName to $newName if not already exists
     *
     * @return CoreError|RepoInfo
     */
    public function repoRename(RepoRenameInput $repoRenameInput): CoreError|RepoInfo;

    /**
     * Delete an existing repository named $name
     *
     * @return CoreError|RepoInfo
     */
    public function repoDelete(RepoDeleteInput $repoDeleteInput): CoreError|RepoInfo;
}
