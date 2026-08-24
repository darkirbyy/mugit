<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Service\CoreExecInterface;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class CoreAPITest extends KernelTestCase
{
    private static CoreExecInterface $coreExec;
    private static string $coreRootPath;
    private static string $coreDataPath;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Retrieve and prepare some parameters
        $parameterBag = self::getContainer()->getParameterBag();
        $projectDir = $parameterBag->get('kernel.project_dir');
        self::$coreRootPath = preg_replace('/ui$/', 'core', $projectDir);
        self::$coreDataPath = Path::canonicalize(Path::join(self::$coreRootPath, $parameterBag->get('core.data')));

        // Start a test instance of the core
        $process = new Process(['docker', 'compose', '--project-directory', self::$coreRootPath, 'up', '-d']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        self::$coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        // Stop the test instance of the core
        $process = new Process(['docker', 'compose', '--project-directory', self::$coreRootPath, 'down']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    #[PU\Test]
    public function repoCreate(): void
    {
        $coreData = self::$coreExec->exec('repo create repo-1');
        $coreData = self::$coreExec->exec('repo create repo-2');
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryExists(Path::join(self::$coreDataPath, 'repo-1.git'));
        $this->assertDirectoryExists(Path::join(self::$coreDataPath, 'repo-2.git'));
    }

    #[PU\Test]
    #[PU\DataProvider('repoFailValues')]
    #[PU\Depends('repoCreate')]
    public function repoFail(string $subcommand, int $expectedExitCode): void
    {
        $coreData = self::$coreExec->exec('repo ' . $subcommand);
        $this->assertSame($expectedExitCode, $coreData->exitCode);
    }

    #[PU\Test]
    #[PU\Depends('repoFail')]
    public function repoRename(): void
    {
        $coreData = self::$coreExec->exec('repo rename repo-2 repo-3');
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryExists(Path::join(self::$coreDataPath, 'repo-3.git'));
        $this->assertDirectoryDoesNotExist(Path::join(self::$coreDataPath, 'repo-2.git'));
    }

    #[PU\Test]
    #[PU\Depends('repoFail')]
    public function repoDelete(): void
    {
        $coreData = self::$coreExec->exec('repo delete repo-1');
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryDoesNotExist(Path::join(self::$coreDataPath, 'repo-1.git'));
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
