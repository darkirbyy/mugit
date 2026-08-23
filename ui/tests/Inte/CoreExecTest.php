<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Service\CoreExecInterface;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class CoreExecTest extends KernelTestCase
{
    private static string $coreRootPath;
    private static string $coreDataPath;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

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

        self::bootKernel();
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
    public function repoCreateSuccess(): void
    {
        $coreExec = self::getContainer()->get(CoreExecInterface::class);

        $coreData = $coreExec->exec('repo create lol');
        $this->assertSame(0, $coreData->exitCode);
        $this->assertDirectoryExists(Path::join(self::$coreDataPath, 'lol.git'));
    }
}
