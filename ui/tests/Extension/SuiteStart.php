<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SuiteStart implements StartedSubscriber
{
    public function __construct() {}

    public function notify(Started $event): void
    {
        $testsuiteName = $event->testSuite()->name();

        // Clean up test data directory and start a test instance of the core
        if ('inte' == $testsuiteName || 'func' == $testsuiteName) {
            $coreRootPath = Path::join(__DIR__, '..', '..', '..', 'core');
            $coreDataPath = Path::join($coreRootPath, $_ENV['CORE_DATA']);

            $filesystem = new Filesystem();
            $filesystem->remove($coreDataPath);
            $filesystem->mkdir($coreDataPath);

            $process = new Process(['docker', 'compose', '--project-directory', $coreRootPath, 'up', '-d']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }

        // Prepare a temporary public directory and compile the assets into it
        if ('func' == $testsuiteName) {
            $uiRootPath = Path::join(__DIR__, '..', '..');
            $pantherPublicPath = Path::join($uiRootPath, $_ENV['PANTHER_WEB_SERVER_DIR']);

            $filesystem = new Filesystem();
            $filesystem->remove($pantherPublicPath);
            $filesystem->mkdir($pantherPublicPath);
            $index = $filesystem->readFile(Path::join($uiRootPath, 'public', 'index.php'));
            $index = str_replace('/vendor', '/../../vendor', $index);
            $filesystem->dumpFile(Path::join($pantherPublicPath, 'index.php'), $index);

            $process = new Process(['npm', 'run', 'test-build']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }
}
