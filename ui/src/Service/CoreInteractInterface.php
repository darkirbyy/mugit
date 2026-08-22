<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreErrorData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;

/**
 * APIv1 layer to the core.
 */
interface CoreInteractInterface
{
    public const REGEX_NAME = '^[a-zA-Z]([a-zA-Z0-9_-]){1,127}$';
    public const REGEX_UUID = '^[0-9a-f]{8}-[0-9a-f]{4}-[13-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
    public const REGEX_KEY = '^[a-zA-Z0-9/+=\\]{68}$';
    public const REGEX_COMMENT = '^[a-zA-Z0-9_@ -]{0,255}$';

    /**
     * List all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically.
     */
    public function repoList(RepoListData $repoListData): ?CoreErrorData;

    /**
     * Create a new repository named $name if not already exists.
     */
    public function repoCreate(RepoCreateData $repoCreateData): ?CoreErrorData;

    /**
     * Rename an existing repository named $oldName to $newName if not already exists.
     */
    public function repoRename(RepoRenameData $repoRenameData): ?CoreErrorData;

    /**
     * Delete an existing repository named $name.
     */
    public function repoDelete(RepoDeleteData $repoDeleteData): ?CoreErrorData;
}
