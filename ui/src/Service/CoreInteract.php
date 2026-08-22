<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreErrorData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoInfoData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use Psr\Log\LoggerInterface;

class CoreInteract implements CoreInteractInterface
{
    public function __construct(private LoggerInterface $logger, private CoreExecInterface $coreExec) {}

    #[\Override]
    public function repoList(RepoListData $repoListData): ?CoreErrorData
    {
        $command = 'repo list';
        $coreOutputData = $this->coreExec->exec($command);

        if ($coreOutputData instanceof CoreErrorData) {
            return $coreOutputData;
        }

        if ($coreOutputData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutputData->exitCode . ').');

            return new CoreErrorData('repo.list.failed');
        }

        if (array_any($coreOutputData->lineList, fn($line) => 2 != count(explode(' ', $line)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore mote line(s) that could not be parsed.');

            return new CoreErrorData('repo.list.failed');
        }

        $repoListData->repoInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);

            return new RepoInfoData($lineExploded[0], (int) $lineExploded[1]);
        }, $coreOutputData->lineList);

        return null;
    }

    #[\Override]
    public function repoCreate(RepoCreateData $repoCreateData): ?CoreErrorData
    {
        throw new \Exception('Not implemented');
    }

    #[\Override]
    public function repoRename(RepoRenameData $repoRenameData): ?CoreErrorData
    {
        $command = 'repo rename ' . $repoRenameData->oldName . ' ' . $repoRenameData->newName;
        $coreOutputData = $this->coreExec->exec($command);

        if ($coreOutputData instanceof CoreErrorData) {
            return $coreOutputData;
        }

        if ($coreOutputData->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutputData->exitCode . ').');

            return new CoreErrorData(
                match ($coreOutputData->exitCode) {
                    3, 4 => 'repo.rename.invalid',
                    7 => 'repo.rename.alreadyExist',
                    default => 'repo.rename.failed',
                },
            );
        }

        return null;
    }

    #[\Override]
    public function repoDelete(RepoDeleteData $repoDeleteData): ?CoreErrorData
    {
        return new CoreErrorData('repo.delete.failed');
    }
}
