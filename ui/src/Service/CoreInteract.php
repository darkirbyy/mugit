<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\ErrorData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoInfoData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\DTO\UserKeysAddData;
use App\DTO\UserKeysInfoData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use Psr\Log\LoggerInterface;

class CoreInteract implements CoreInteractInterface
{
    public function __construct(private LoggerInterface $logger, private CoreExecInterface $coreExec) {}

    #[\Override]
    public function repoList(RepoListData $repoListData): ?ErrorData
    {
        $command = 'repo list';
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData('repo.list.failed');
        }

        if (array_any($coreData->lineList, fn($line) => 2 != count(explode(' ', $line)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore more line(s) that could not be parsed.');

            return new ErrorData('repo.list.failed');
        }

        $repoListData->repoInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);

            return new RepoInfoData($lineExploded[0], (int) $lineExploded[1]);
        }, $coreData->lineList);

        return null;
    }

    #[\Override]
    public function repoCreate(RepoCreateData $repoCreateData): ?ErrorData
    {
        $command = 'repo create ' . $repoCreateData->name;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData(
                match ($coreData->exitCode) {
                    3 => 'repo.create.empty',
                    4 => 'repo.create.invalid',
                    7 => 'repo.create.alreadyExist',
                    default => 'repo.create.failed',
                },
            );
        }

        return null;
    }

    #[\Override]
    public function repoRename(RepoRenameData $repoRenameData): ?ErrorData
    {
        $command = 'repo rename ' . $repoRenameData->oldName . ' ' . $repoRenameData->newName;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData(
                match ($coreData->exitCode) {
                    3 => 'repo.rename.empty',
                    4 => 'repo.rename.invalid',
                    7 => 'repo.rename.alreadyExist',
                    default => 'repo.rename.failed',
                },
            );
        }

        return null;
    }

    #[\Override]
    public function repoDelete(RepoDeleteData $repoDeleteData): ?ErrorData
    {
        $command = 'repo delete ' . $repoDeleteData->name;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData('repo.delete.failed');
        }

        return null;
    }

    #[\Override]
    public function userKeysList(UserKeysListData $userKeysListData): ?ErrorData
    {
        $command = 'user key-list ' . $userKeysListData->uuid;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData('user.keys.list.failed');
        }

        if (array_any($coreData->lineList, fn($line) => 2 > count(explode(' ', $line)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore more line(s) that could not be parsed.');

            return new ErrorData('user.keys.list.failed');
        }

        $userKeysListData->userKeysInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);

            return new UserKeysInfoData($lineExploded[0], \DateTime::createFromTimestamp((int) $lineExploded[1]), count($lineExploded) > 2 ? $lineExploded[2] : null);
        }, $coreData->lineList);

        return null;
    }

    #[\Override]
    public function userKeysAdd(UserKeysAddData $userKeysAddData): ?ErrorData
    {
        $key = substr($userKeysAddData->fullKey, 12, 68);
        $comment = substr($userKeysAddData->fullKey, 81);
        $command = 'user key-add ' . $userKeysAddData->uuid . ' \'' . $key . '\' ' . $comment;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData(
                match ($coreData->exitCode) {
                    3 => 'user.keys.add.empty',
                    4, 5 => 'user.keys.add.invalid',
                    9 => 'user.keys.add.alreadyExist',
                    default => 'user.keys.add.failed',
                },
            );
        }

        return null;
    }

    #[\Override]
    public function userKeysRemove(UserKeysRemoveData $userKeysRemoveData): ?ErrorData
    {
        $command = 'user key-remove ' . $userKeysRemoveData->uuid . ' ' . $userKeysRemoveData->key;
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            return new ErrorData('user.keys.remove.failed');
        }

        return null;
    }
}
