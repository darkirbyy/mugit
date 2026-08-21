<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
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
    public function repoList(RepoListData $repoListData): ?CoreError
    {
        $command = 'repo list';
        $coreOutput = $this->coreExec->exec($command);

        if ($coreOutput instanceof CoreError) {
            return $coreOutput;
        }

        if ($coreOutput->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutput->exitCode . ').');

            return new CoreError('repo.list.failed');
        }

        if (array_any($coreOutput->lines, fn($line) => 2 != count(explode(' ', $line)))) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore mote line(s) that could not be parsed.');

            return new CoreError('repo.list.failed');
        }

        $repoListData->repoInfoDataList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);

            return new RepoInfoData($lineExploded[0], (int) $lineExploded[1]);
        }, $coreOutput->lines);

        return null;
    }

    #[\Override]
    public function repoCreate(RepoCreateData $repoCreateData): ?CoreError
    {
        throw new \Exception('Not implemented');
    }

    #[\Override]
    public function repoRename(RepoRenameData $repoRenameData): ?CoreError
    {
        $command = 'repo rename ' . $repoRenameData->oldName . ' ' . $repoRenameData->newName;
        $coreOutput = $this->coreExec->exec($command);

        if ($coreOutput instanceof CoreError) {
            return $coreOutput;
        }

        if ($coreOutput->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutput->exitCode . ').');

            return new CoreError(
                match ($coreOutput->exitCode) {
                    3, 4 => 'repo.rename.invalid',
                    7 => 'repo.rename.alreadyExist',
                    default => 'repo.rename.failed',
                },
            );
        }

        return null;
    }

    #[\Override]
    public function repoDelete(RepoDeleteData $repoDeleteData): ?CoreError
    {
        return new CoreError('repo.delete.failed');
    }
}
