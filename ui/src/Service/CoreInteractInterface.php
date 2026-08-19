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
    const REGEX_NAME = '^[a-zA-Z]([a-zA-Z0-9_-]){1,127}$';
    const REGEX_UUID = '^[0-9a-f]{8}-[0-9a-f]{4}-[13-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
    const REGEX_KEY = '^[a-zA-Z0-9/+=\\]{68}$';
    const REGEX_COMMENT = '^[a-zA-Z0-9_@ -]{0,255}$';

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
