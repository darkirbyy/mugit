<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoInfo;
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
    public function repoList(): CoreError|array
    {
        $coreOutput = $this->coreExec->exec('repo list');

        if($coreOutput instanceof CoreError){
            return $coreOutput;
        }

        if ($coreOutput->exitCode > 0) {
            $this->logger->error(self::class .':: the command `repo list` returned a non zero exit code (' . $coreOutput->exitCode . ').');
            return new CoreError('listFailed');
        }

        if (array_any($coreOutput->lines, fn($line) =>  count(explode(' ', $line)) != 2)) {
            $this->logger->error(self::class .':: the command `repo list` returned one ore mote line(s) that could not be parsed.');
            return new CoreError('listFailed');
        }

        $repoInfoList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);
            return new RepoInfo($lineExploded[0], (int)$lineExploded[1]);
        }, $coreOutput->lines);

        return $repoInfoList;
    }

    #[Override]
    public function repoCreate(RepoCreateInput $repoCreateInput): CoreError|RepoInfo
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function repoRename(RepoRenameInput $repoRenameInput): CoreError|RepoInfo
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function repoDelete(RepoDeleteInput $repoDeleteInput): CoreError|RepoInfo
    {
        throw new \Exception('Not implemented');
    }
}
