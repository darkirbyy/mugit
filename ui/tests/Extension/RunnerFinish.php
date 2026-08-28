<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Event\TestRunner\Finished;
use PHPUnit\Event\TestRunner\FinishedSubscriber;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class RunnerFinish implements FinishedSubscriber
{
    public function __construct(private array $suiteNameList) {}

    public function notify(Finished $event): void
    {
        $coreRootPath = realpath(__DIR__ . '/../../../core');

        // INTE, FUNC, E2E ONLY : stop the test instance of the core
        if (!empty(array_intersect($this->suiteNameList, TestsExtension::SUITE_REQUIRE_CORE))) {
            $process = new Process(['docker', 'compose', '--project-directory', $coreRootPath, 'down']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }
}
