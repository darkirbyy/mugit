<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\Configuration\TestSuite;

final class TestsExtension implements Extension
{
    public const array SUITE_REQUIRE_ASSETS = ['func', 'e2e'];
    public const array SUITE_REQUIRE_CORE = ['inte', 'func', 'e2e'];

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (!empty($configuration->includeTestSuites())) {
            $suiteNameList = $configuration->includeTestSuites();
        } elseif (!empty($configuration->excludeTestSuites())) {
            $suiteNameList = array_diff($this->getAllSuiteNames($configuration), $configuration->excludeTestSuites());
        } else {
            $suiteNameList = $this->getAllSuiteNames($configuration);
        }
        $facade->registerSubscribers(new RunnerStart($suiteNameList), new SuiteStart(), new RunnerFinish($suiteNameList));
    }

    private function getAllSuiteNames(Configuration $configuration): array
    {
        return array_map(fn(TestSuite $t) => $t->name(), $configuration->testSuite()->asArray());
    }
}
