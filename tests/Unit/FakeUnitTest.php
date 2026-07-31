<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;

final class FakeUnitTest extends TestCase
{
    #[PU\Test]
    public function fake(): void
    {
        $test = 1;

        $this->assertSame($test, 1);
    }
}
