<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoInfo;
use App\DTO\RepoListOutput;
use App\DTO\RepoRenameInput;
use Override;
use Psr\Log\LoggerInterface;

class CoreInteract implements CoreInteractInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private CoreExecInterface $coreExec,
    ) {}

    #[Override]
    public function repoList(): CoreError|RepoListOutput
    {
        $command = 'repo list';
        $coreOutput = $this->coreExec->exec($command);

        if ($coreOutput instanceof CoreError) {
            return $coreOutput;
        }

        if ($coreOutput->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutput->exitCode . ').');
            return new CoreError('listFailed');
        }

        if (array_any($coreOutput->lines, fn($line) =>  count(explode(' ', $line)) != 2)) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned one ore mote line(s) that could not be parsed.');
            return new CoreError('listFailed');
        }

        $repoListOutput = new RepoListOutput(array_map(function ($line) {
            $lineExploded = explode(' ', $line);
            return new RepoInfo($lineExploded[0], (int)$lineExploded[1]);
        }, $coreOutput->lines));

        return $repoListOutput;
    }

    #[Override]
    public function repoCreate(RepoCreateInput $repoCreateInput): CoreError|true
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function repoRename(RepoRenameInput $repoRenameInput): CoreError|true
    {
        $command = 'repo rename ' . $repoRenameInput->oldName . ' ' . $repoRenameInput->newName;
        $coreOutput = $this->coreExec->exec($command);

        if ($coreOutput instanceof CoreError) {
            return $coreOutput;
        }

        if ($coreOutput->exitCode > 0) {
            $this->logger->error(self::class . ':: the command `' . $command . '` returned a non zero exit code (' . $coreOutput->exitCode . ').');
            return new CoreError(match ($coreOutput->exitCode) {
                3, 4 => 'renameInvalid',
                7 => 'renameAlreadyExist',
                default => 'renameFailed',
            });
        }

        return true;
    }

    #[Override]
    public function repoDelete(RepoDeleteInput $repoDeleteInput): CoreError|true
    {
        throw new \Exception('Not implemented');
    }
}
