<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FakeInteTest extends KernelTestCase
{
    #[PU\Test]
    public function fake(): void
    {
        $this->assertSame(true, true);
    }
}
