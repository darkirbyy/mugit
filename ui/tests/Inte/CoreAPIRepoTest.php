<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use PHPUnit\Framework\Attributes as PU;

final class CoreAPIRepoTest extends CoreAPITest
{
    #[PU\Test]
    public function repoList(): void
    {
        self::coreRepoAdd('repo-1', 'repo-2');

        $coreData = self::$coreExec->exec('repo list');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount(2, $coreData->lineList);
        $this->assertStringStartsWith('repo-1', $coreData->lineList[0]);
        $this->assertStringStartsWith('repo-2', $coreData->lineList[1]);
    }

    #[PU\Test]
    public function repoCreate(): void
    {
        $coreData = self::$coreExec->exec('repo create repo-1');

        $logContent = self::coreLogContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryExists(self::coreRepoPath('repo-1'));
        $this->assertDirectoryExists(self::coreRepoPath('repo-1'));
        $this->assertSame(1, substr_count($logContent, "\n"));
    }

    #[PU\Test]
    public function repoRename(): void
    {
        self::coreRepoAdd('repo-1');

        $coreData = self::$coreExec->exec('repo rename repo-1 repo-2');

        $logContent = self::coreLogContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryDoesNotExist(self::coreRepoPath('repo-1'));
        $this->assertDirectoryExists(self::coreRepoPath('repo-2'));
        $this->assertSame(1, substr_count($logContent, "\n"));
    }

    #[PU\Test]
    public function repoDelete(): void
    {
        self::coreRepoAdd('repo-1');

        $coreData = self::$coreExec->exec('repo delete repo-1');
        $logContent = self::coreLogContent();

        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryDoesNotExist(self::coreRepoPath('repo-1'));
        $this->assertSame(1, substr_count($logContent, "\n"));
    }

    #[PU\Test]
    #[PU\DataProvider('repoFailValues')]
    public function repoFail(string $subcommand, int $expectedExitCode): void
    {
        self::coreRepoAdd('repo-1', 'repo-2');

        $coreData = self::$coreExec->exec('repo ' . $subcommand);

        $this->assertSame($expectedExitCode, $coreData->exitCode);
    }

    public static function repoFailValues(): array
    {
        return [
            'missing subcommand' => ['', 1],
            'invalid subcommand' => ['remove', 2],
            'create, empty name' => ['create', 3],
            'create, invalid name' => ['create repo@3', 4],
            'create, already exist name' => ['create repo-1', 7],
            'rename, empty new name' => ['rename repo-1', 3],
            'rename, invalid new name' => ['rename repo-1 repo@3', 4],
            'rename, does not exist old name' => ['rename repo-3 repo-1', 6],
            'rename, already exist new name' => ['rename repo-1 repo-2', 7],
            'delete, empty name' => ['delete', 3],
            'delete, invalid name' => ['delete repo@3', 4],
            'delete, does not exist name' => ['delete repo-3', 6],
        ];
    }
}
