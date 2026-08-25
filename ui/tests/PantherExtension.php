<?php

namespace App\Tests;

use PHPUnit\Event\Test\AfterTestMethodFinished;
use PHPUnit\Event\Test\AfterTestMethodFinishedSubscriber;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class PantherExtension implements AfterTestMethodFinishedSubscriber, Extension
{
    public const COVERAGE_DIRECTORY = __DIR__ . '/../var/cache/panther';

    public function notify(AfterTestMethodFinished $event): void
    {
        if (!is_dir(self::COVERAGE_DIRECTORY)) {
            return;
        }

        $files = (new Finder())->in(self::COVERAGE_DIRECTORY)->files()->name('*.code_coverage');
        foreach ($files as $file) {
            $coverageId = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $content = $file->getContents();
            $rawCodeCoverageData = unserialize($content);

            if (!empty($content)) {
                CodeCoverage::instance()->codeCoverage()->append($rawCodeCoverageData, $coverageId);
            }
        }
        (new Filesystem())->remove(self::COVERAGE_DIRECTORY);
    }

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber($this);
    }
}
