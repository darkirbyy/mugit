<?php

declare(strict_types=1);

namespace App\Tests\End2End;

use App\Service\CoreExecInterface;
use App\Tests\Mock\KeycloakMockUserCreate;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class End2EndControllerTest extends PantherTestCase
{
    protected CoreExecInterface $coreExec;
    protected Client $client;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createPantherClient();
        $this->coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    public function login(bool $isAdmin): void
    {
        $user = self::getContainer()->get(KeycloakMockUserCreate::class)->createUser($isAdmin);
        $this->client->loginUser($user);
    }
}
