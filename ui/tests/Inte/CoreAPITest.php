<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Tests\Extension\CoreAwareTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class CoreAPITest extends KernelTestCase
{
    use CoreAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::coreInitAndReset();
    }
}
