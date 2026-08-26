<?php

declare(strict_types=1);

namespace App\Tests\Func;

use App\Tests\Mock\KeycloakMockEntryPoint;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class FuncControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    public function login(bool $isAdmin): void
    {
        $session = $this->client->getSession();
        self::getContainer()->get(KeycloakMockEntryPoint::class)->loginFakeUser($session, $isAdmin);
    }
}
