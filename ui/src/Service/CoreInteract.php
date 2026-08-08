<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RepoCreateInput;
use App\DTO\RepoDeleteInput;
use App\DTO\RepoInfo;
use App\DTO\RepoRenameInput;
use Override;
use RuntimeException;

class CoreInteract implements CoreInteractInterface
{
    public function __construct(
        private CoreExecInterface $coreExec,
    ) {}

    #[Override]
    public function repoList(): false|array
    {
        $coreOutput = $this->coreExec->exec('repo list');

        if ($coreOutput->exitCode > 0) {
            throw new RuntimeException('TODO');
        }

        $repoInfoList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);
            if (count($lineExploded) < 2) {
                throw new RuntimeException('TODO');
            }
            return new RepoInfo($lineExploded[0], (int)$lineExploded[1]);
        }, $coreOutput->lines);

        return $repoInfoList;
    }

    #[Override]
    public function repoCreate(RepoCreateInput $repoCreateInput): false|RepoInfo
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function repoRename(RepoRenameInput $repoRenameInput): false|RepoInfo
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function repoDelete(RepoDeleteInput $repoDeleteInput): false|RepoInfo
    {
        throw new \Exception('Not implemented');
    }
}
