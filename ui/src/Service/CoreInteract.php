<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreData;
use App\DTO\ErrorData;
use App\DTO\LogInfoData;
use App\DTO\LogListData;
use App\DTO\LogSizeData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoInfoData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\DTO\UserDeleteData;
use App\DTO\UserInfoData;
use App\DTO\UserKeysAddData;
use App\DTO\UserKeysInfoData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\DTO\UserListData;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreInteract implements CoreInteractInterface
{
    public function __construct(#[Autowire('%core.log_default_length%')] private int $coreDefaultLength, private LoggerInterface $logger, private CoreExecInterface $coreExec) {}

    #[\Override]
    public function repoList(RepoListData $repoListData): ?ErrorData
    {
        $command = 'repo list';
        if (($coreData = $this->executeCommand($command, 'repo.list.failed')) instanceof ErrorData) {
            return $coreData;
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
        $exitCodeToErrorTextKey = [3 => 'repo.create.empty', 4 => 'repo.create.invalid', 7 => 'repo.create.alreadyExist'];
        if (($coreData = $this->executeCommand($command, 'repo.create.failed', $exitCodeToErrorTextKey)) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function repoRename(RepoRenameData $repoRenameData): ?ErrorData
    {
        $command = 'repo rename ' . $repoRenameData->oldName . ' ' . $repoRenameData->newName;
        $exitCodeToErrorTextKey = [3 => 'repo.rename.empty', 4 => 'repo.rename.invalid', 7 => 'repo.rename.alreadyExist'];
        if (($coreData = $this->executeCommand($command, 'repo.rename.failed', $exitCodeToErrorTextKey)) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function repoDelete(RepoDeleteData $repoDeleteData): ?ErrorData
    {
        $command = 'repo delete ' . $repoDeleteData->name;
        if (($coreData = $this->executeCommand($command, 'repo.delete.failed')) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function userList(UserListData $userListData): ?ErrorData
    {
        $command = 'user list';
        if (($coreData = $this->executeCommand($command, 'user.list.failed')) instanceof ErrorData) {
            return $coreData;
        }

        $userListData->userInfoDataList = array_map(function ($line) {
            return new UserInfoData($line);
        }, $coreData->lineList);

        return null;
    }

    #[\Override]
    public function userKeysList(UserKeysListData $userKeysListData): ?ErrorData
    {
        $command = 'user key-list ' . $userKeysListData->uuid;
        if (($coreData = $this->executeCommand($command, 'user.keys.list.failed')) instanceof ErrorData) {
            return $coreData;
        }

        if (array_any($coreData->lineList, fn($line) => 2 > count(explode(' ', $line)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore more line(s) that could not be parsed.');

            return new ErrorData('user.keys.list.failed');
        }

        $userKeysListData->userKeysInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line, 3);

            return new UserKeysInfoData($lineExploded[0], \DateTime::createFromTimestamp((int) $lineExploded[1]), count($lineExploded) > 2 ? $lineExploded[2] : null);
        }, $coreData->lineList);

        return null;
    }

    #[\Override]
    public function userKeysAdd(UserKeysAddData $userKeysAddData): ?ErrorData
    {
        $key = substr($userKeysAddData->fullKey, 12, 68);
        $comment = substr($userKeysAddData->fullKey, 81);
        $command = 'user key-add ' . $userKeysAddData->uuid . ' \'' . $key . '\' \'' . $comment . '\'';
        $exitCodeToErrorTextKey = [3 => 'user.keys.add.empty', 4 => 'user.keys.add.invalid', 5 => 'user.keys.add.invalid', 9 => 'user.keys.add.alreadyExist'];
        if (($coreData = $this->executeCommand($command, 'user.keys.add.failed', $exitCodeToErrorTextKey)) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function userKeysRemove(UserKeysRemoveData $userKeysRemoveData): ?ErrorData
    {
        $command = 'user key-remove ' . $userKeysRemoveData->uuid . ' ' . $userKeysRemoveData->key;
        if (($coreData = $this->executeCommand($command, 'user.keys.remove.failed')) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function userDelete(UserDeleteData $userDeleteData): ?ErrorData
    {
        $command = 'user delete ' . $userDeleteData->uuid;
        if (($coreData = $this->executeCommand($command, 'user.delete.failed')) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    #[\Override]
    public function logSize(LogSizeData $logSizeData): ?ErrorData
    {
        $command = 'log size';
        if (($coreData = $this->executeCommand($command, 'log.size.failed')) instanceof ErrorData) {
            return $coreData;
        }

        if (1 !== count($coreData->lineList) && !is_int($coreData->lineList[0])) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a line that could not be parsed.');

            return new ErrorData('log.size.failed');
        }

        $logSizeData->size = (int) $coreData->lineList[0];

        return null;
    }

    #[\Override]
    public function logList(LogListData $logListData): ?ErrorData
    {
        $command = 'log list ' . ($logListData->offset ?? 1) . ' ' . ($logListData->length ?? $this->coreDefaultLength);
        if (($coreData = $this->executeCommand($command, 'log.list.failed')) instanceof ErrorData) {
            return $coreData;
        }

        if (array_any($coreData->lineList, fn($line) => 3 !== count(explode(' ', $line, 3)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore more line(s) that could not be parsed.');

            return new ErrorData('log.list.failed');
        }

        $logListData->logInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line, 3);

            return new LogInfoData(\DateTime::createFromTimestamp((int) $lineExploded[0]), $lineExploded[1], $lineExploded[2]);
        }, $coreData->lineList);

        return null;
    }

    #[\Override]
    public function logPurge(): ?ErrorData
    {
        $command = 'log purge';
        if (($coreData = $this->executeCommand($command, 'log.purge.failed')) instanceof ErrorData) {
            return $coreData;
        }

        return null;
    }

    private function executeCommand(string $command, string $defaultErrorTextKey, ?array $exitCodeToErrorTextKey = null): ErrorData|CoreData
    {
        $coreData = $this->coreExec->exec($command);

        if (null === $coreData) {
            return new ErrorData('git.connectionFailed');
        }

        if ($coreData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreData->exitCode . ').');

            if (null != $exitCodeToErrorTextKey and array_key_exists($coreData->exitCode, $exitCodeToErrorTextKey)) {
                return new ErrorData($exitCodeToErrorTextKey[$coreData->exitCode]);
            }

            return new ErrorData($defaultErrorTextKey);
        }

        return $coreData;
    }
}
