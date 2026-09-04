<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

final class SuiteStart implements StartedSubscriber
{
    public function __construct() {}

    public function notify(Started $event): void
    {
        $finder = new Finder();
        $filesystem = new Filesystem();
        $coreRootPath = realpath(__DIR__ . '/../../../core');

        // INTE, FUNC, E2E ONLY : clean up core data directory before the suite statr
        if (in_array($event->testSuite()->name(), TestsExtension::SUITE_REQUIRE_CORE)) {
            // todo factor with core aware trait -> reset
            $coreDataPath = Path::join($coreRootPath, $_ENV['CORE_DATA']);

            // Clear all git directories
            $finder->directories()->name('*.git')->depth('== 0')->in($coreDataPath);
            if ($finder->hasResults()) {
                foreach ($finder as $file) {
                    $filesystem->remove($file->getPathname());
                }
            }

            // Clear the authorized_keys and log files
            $filesystem->dumpFile(Path::join($coreDataPath, '.ssh', 'authorized_keys'), '');
            $filesystem->dumpFile(Path::join($coreDataPath, 'logs'), '');
        }
    }
}
