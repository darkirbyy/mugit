<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

final class FakeFuncTest extends WebTestCase
{
    use Factories;

    #[PU\Test]
    public function fake(): void
    {
        $client = static::createClient();
        $client->request('GET', '');
        $this->assertResponseIsSuccessful();
    }
}
