<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Tests\Extension\CoreAwareTrait;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CoreAPITest extends KernelTestCase
{
    use CoreAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::coreInit();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::coreReset();
    }

    #[PU\Test]
    public function repoCreate(): void
    {
        $coreData = self::$coreExec->exec('repo create repo-1');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryExists(self::coreRepoPath('repo-1'));
    }

    #[PU\Test]
    public function repoRename(): void
    {
        self::coreRepoAdd('repo-1');

        $coreData = self::$coreExec->exec('repo rename repo-1 repo-2');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryDoesNotExist(self::coreRepoPath('repo-1'));
        $this->assertDirectoryExists(self::coreRepoPath('repo-2'));
    }

    #[PU\Test]
    public function repoDelete(): void
    {
        self::coreRepoAdd('repo-1');

        $coreData = self::$coreExec->exec('repo delete repo-1');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryDoesNotExist(self::coreRepoPath('repo-1'));
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
            'no subcommand' => ['', 1],
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
