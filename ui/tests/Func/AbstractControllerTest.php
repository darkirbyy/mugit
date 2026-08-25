<?php

declare(strict_types=1);

namespace App\Tests\Func;

use App\Service\CoreExecInterface;
use App\Tests\Mock\KeycloakMockUserCreate;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractControllerTest extends WebTestCase
{
    protected CoreExecInterface $coreExec;
    protected KernelBrowser $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    public function login(bool $isAdmin): void
    {
        $user = self::getContainer()->get(KeycloakMockUserCreate::class)->createUser($isAdmin);
        $this->client->loginUser($user);
    }
}
