<?php

declare(strict_types=1);

namespace App\Tests\Func;

use App\Tests\Extension\CoreAwareTrait;
use App\Tests\Mock\KeycloakMockEntryPoint;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FuncControllerTest extends WebTestCase
{
    use CoreAwareTrait;

    protected KernelBrowser $client;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        self::coreInit();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::coreReset();
    }

    public function login(bool $isAdmin): void
    {
        self::getContainer()->get(KeycloakMockEntryPoint::class)->loginFakeUser($this->client->getSession(), $isAdmin);
    }
}
