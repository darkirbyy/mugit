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
            $coreDataPath = Path::join($coreRootPath, $_ENV['CORE_DATA']);

            $finder->directories()->name('*.git')->depth('== 0')->in($coreDataPath);
            if (!$finder->hasResults()) {
                return;
            }

            foreach ($finder as $file) {
                $filesystem->remove($file->getPathname());
            }
        }
    }
}
