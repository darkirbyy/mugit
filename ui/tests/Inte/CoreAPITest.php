<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Tests\Extension\CoreAwareTrait;
use App\Tests\Mock\KeycloakMockEntryPoint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;

abstract class CoreAPITest extends KernelTestCase
{
    use CoreAwareTrait;
    protected static Security $securityMock;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        // Stub the user for the integration test
        self::$securityMock = self::createStub(Security::class);
        self::$securityMock->method('getUser')->willReturn(KeycloakMockEntryPoint::createTestUser(1, false));
        self::getContainer()->set(Security::class, self::$securityMock);

        self::coreInitAndReset();
    }
}
