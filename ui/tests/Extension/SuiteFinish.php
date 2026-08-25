<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class SuiteFinish implements FinishedSubscriber
{
    public function __construct() {}

    public function notify(Finished $event): void
    {
        $testsuiteName = $event->testSuite()->name();

        // Stop the test instance of the core
        if ('inte' == $testsuiteName || 'func' == $testsuiteName) {
            $coreRootPath = Path::join(__DIR__, '..', '..', '..', 'core');

            $process = new Process(['docker', 'compose', '--project-directory', $coreRootPath, 'down']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }

        if ('func' == $testsuiteName) {
            // todo : npm clean ?
        }
    }
}
