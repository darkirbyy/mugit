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

        // Compile the assets for E2E testing via panther
        if ('func' == $testsuiteName) {
            // todo : npm run test-build
        }
    }
}
