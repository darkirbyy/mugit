<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ErrorData;
use App\DTO\LogListData;
use App\DTO\LogSizeData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\DTO\UserDeleteData;
use App\DTO\UserKeysAddData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\DTO\UserListData;

/**
 * API layer to the core.
 */
interface CoreInteractInterface
{
    public const REGEX_NAME = '^[a-zA-Z]([a-zA-Z0-9_-]){1,127}$';
    public const REGEX_UUID = '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$';
    public const REGEX_KEY = '^[a-zA-Z0-9\/+=\\\]{68}$';
    public const REGEX_FULL_KEY = '^ssh-ed25519 [a-zA-Z0-9\/+=\\\]{68}( [a-zA-Z0-9_@ -]{0,255}|)$';

    /**
     * List all repositories names (without the `git` suffix) and sizes (in Kio), sorted alphabetically.
     */
    public function repoList(RepoListData $repoListData): ?ErrorData;

    /**
     * Create a new repository named $name if not already exists.
     */
    public function repoCreate(RepoCreateData $repoCreateData): ?ErrorData;

    /**
     * Rename an existing repository named $oldName to $newName if not already exists.
     */
    public function repoRename(RepoRenameData $repoRenameData): ?ErrorData;

    /**
     * Delete an existing repository named $name.
     */
    public function repoDelete(RepoDeleteData $repoDeleteData): ?ErrorData;

    /**
     * List all SSH keys, date and comment for a given user.
     */
    public function userList(UserListData $userListData): ?ErrorData;

    /**
     * List all SSH keys, date and comment for a given user.
     */
    public function userKeysList(UserKeysListData $userKeysListData): ?ErrorData;

    /**
     * Remove a new SSH keys for a given user.
     */
    public function userKeysAdd(UserKeysAddData $userKeysAddData): ?ErrorData;

    /**
     * Remove an existing SSH keys for a given user.
     */
    public function userKeysRemove(UserKeysRemoveData $userKeysRemoveData): ?ErrorData;

    /**
     * Remove an existing SSH keys for a given user.
     */
    public function userDelete(UserDeleteData $userDeleteData): ?ErrorData;

    /**
     * Count the number of logs.
     */
    public function logSize(LogSizeData $logSizeData): ?ErrorData;

    /**
     * List a subset of the logs with the date, the uuid and the command executed.
     */
    public function logList(LogListData $logListData): ?ErrorData;

    /**
     * Purge all logs.
     */
    public function logPurge(): ?ErrorData;
}
