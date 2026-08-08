<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoInfo;
use App\DTO\RepoRenameInput;

interface CoreInteractInterface
{
    /**
     * List all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically
     *
     * @return false|RepoInfo[]
     */
    public function repoList(): false|array;

    /**
     * Create a new repository named $name if not already exists
     *
     * @return false|RepoInfo
     */
    public function repoCreate(RepoCreateInput $repoCreateInput): false|RepoInfo;

    /**
     * Rename an existing repository named $oldName to $newName if not already exists
     *
     * @return false|RepoInfo
     */
    public function repoRename(RepoRenameInput $repoRenameInput): false|RepoInfo;

    /**
     * Delete an existing repository named $name
     *
     * @return false|RepoInfo
     */
    public function repoDelete(RepoDeleteInput $repoDeleteInput): false|RepoInfo;
}
