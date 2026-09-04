<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Tests\Extension\CoreAwareTrait;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CoreAPILogTest extends KernelTestCase
{
    use CoreAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::coreInit();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::coreReset();
    }

    #[PU\Test]
    public function logSize(): void
    {
        self::coreLogAdd(1, 'command 1', 'command 2', 'command 3');

        $coreData = self::$coreExec->exec('log size');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount(1, $coreData->lineList);
        $this->assertEquals(3, $coreData->lineList[0]);
    }

    #[PU\Test]
    #[PU\DataProvider('logListValues')]
    public function logList(?int $offset, ?int $length, ?int $expectedStart, int $expectedLength): void
    {
        self::coreLogAdd(1, ...array_map(fn(int $i) => 'command ' . $i, range(1, 100)));

        $coreData = self::$coreExec->exec('log list ' . $offset . ' ' . $length);

        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount($expectedLength, $coreData->lineList);
        if (null !== $expectedStart) {
            $this->assertStringEndsWith('command ' . $expectedStart, $coreData->lineList[0]);
        }
    }

    #[PU\Test]
    public function logPurge(): void
    {
        self::coreLogAdd(1, 'command 1', 'command 2', 'command 3');

        $coreData = self::$coreExec->exec('log purge');

        $logContent = self::coreLogContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertSame(0, substr_count($logContent, "\n"));
    }

    #[PU\Test]
    #[PU\DataProvider('logFailValues')]
    public function logFail(string $subcommand, int $expectedExitCode): void
    {
        self::coreLogAdd(1, 'command 1', 'command 2', 'command 3');

        $coreData = self::$coreExec->exec('log ' . $subcommand);

        $this->assertSame($expectedExitCode, $coreData->exitCode);
    }

    public static function logListValues(): array
    {
        return [
            'default' => [null, null, 1, 50],
            'offset ok' => [10, null, 10, 50],
            'offset ok, length ok' => [90, 5, 90, 5],
            'offset overflow, length ok' => [101, 5, null, 0],
            'offset ok, length overflow' => [90, 15, 90, 11],
        ];
    }

    public static function logFailValues(): array
    {
        return [
            'missing subcommand' => ['', 1],
            'invalid subcommand' => ['count', 2],
            'list, offset not a number' => ['list end', 5],
            'list, length not a number' => ['list 5 default', 5],
            'list, offset zero' => ['list 0', 5],
            'list, length too big' => ['list 1 20000', 5],
        ];
    }
}
