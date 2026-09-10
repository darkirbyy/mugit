<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\CoreData;
use App\Service\CoreExecSocket;
use App\Service\SocketConnector;
use App\Tests\Mock\KeycloakMockEntryPoint;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class CoreExecSocketTest extends TestCase
{
    private static UserInterface $mockUser;
    private mixed $socketClient;
    private mixed $socketServer;
    private SocketConnector $socketConnector;
    private Security $security;
    private LoggerInterface $logger;
    private CoreExecSocket $coreExecSocket;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$mockUser = KeycloakMockEntryPoint::createTestUser(1, false);
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        [$this->socketClient, $this->socketServer] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);

        $this->security = $this->createMock(Security::class);
        $this->socketConnector = $this->createMock(SocketConnector::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->coreExecSocket = new CoreExecSocket($this->socketConnector, $this->security, $this->logger);
    }

    #[PU\Test]
    public function connectFail(): void
    {
        $this->socketConnector->expects($this->once())->method('connect')->willReturn(false);
        $this->security->expects($this->never())->method($this->anything());
        $this->logger->expects($this->once())->method('error');

        $coreData = $this->coreExecSocket->exec('');

        $this->assertNull($coreData);
    }

    #[PU\Test]
    public function responseNoExitCode(): void
    {
        $this->prepareSocketServerResponse([]);
        $this->socketConnector->expects($this->once())->method('connect')->willReturn($this->socketClient);
        $this->security->expects($this->once())->method('getUser')->willReturn(self::$mockUser);
        $this->logger->expects($this->once())->method('error');

        $coreData = $this->coreExecSocket->exec('');

        $this->assertNull($coreData);
    }

    #[PU\Test]
    public function success(): void
    {
        $this->prepareSocketServerResponse(['0', 'Line1', 'Line2']);
        $this->socketConnector->expects($this->once())->method('connect')->willReturn($this->socketClient);
        $this->security->expects($this->once())->method('getUser')->willReturn(self::$mockUser);
        $this->logger->expects($this->never())->method($this->anything());

        $coreData = $this->coreExecSocket->exec('');

        $this->assertInstanceOf(CoreData::class, $coreData);
        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount(2, $coreData->lineList);
        $this->assertArraysAreEqual(['Line1', 'Line2'], $coreData->lineList);
    }

    private function prepareSocketServerResponse(array $lineList): void
    {
        fwrite($this->socketServer, implode("\n", $lineList) . "\n");
        stream_socket_shutdown($this->socketServer, STREAM_SHUT_WR);
    }
}
