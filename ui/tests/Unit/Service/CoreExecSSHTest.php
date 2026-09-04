<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\CoreData;
use App\Service\CoreExecSSH;
use App\Tests\Mock\KeycloakMockEntryPoint;
use phpseclib4\Crypt\EC;
use phpseclib4\Exception\TimeoutException;
use phpseclib4\Net\SSH2;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

final class CoreExecSSHTest extends TestCase
{
    private static string $prikey;
    private static UserInterface $mockUser;
    private SSH2 $ssh;
    private Security $security;
    private LoggerInterface $logger;
    private CoreExecSSH $coreExecSSH;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$prikey = EC::createKey('Ed25519')->toString('openssh');
        self::$mockUser = KeycloakMockEntryPoint::createTestUser(1, false);
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->ssh = $this->createMock(SSH2::class);
        $this->security = $this->createMock(Security::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->coreExecSSH = new CoreExecSSH('host-pubkey-true', self::$prikey, $this->ssh, $this->security, $this->logger);
    }

    #[PU\Test]
    public function authenticateAlreadyAuthenticated(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(true);
        $this->ssh->expects($this->once())->method('exec')->willReturn('Line1');
        $this->ssh->expects($this->once())->method('getExitStatus')->willReturn(0);
        $this->security->expects($this->once())->method('getUser')->willReturn(self::$mockUser);
        $this->logger->expects($this->once())->method('info');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertInstanceOf(CoreData::class, $coreData);
    }

    #[PU\Test]
    public function authenticateWrongHostPubkey(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(false);
        $this->ssh->expects($this->once())->method('getServerPublicHostKey')->willReturn('host-pubkey-false');
        $this->security->expects($this->never())->method($this->anything());
        $this->logger->expects($this->once())->method('error');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertNull($coreData);
    }

    #[PU\Test]
    public function authenticateWrongRootPrikey(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(false);
        $this->ssh->expects($this->once())->method('getServerPublicHostKey')->willReturn('host-pubkey-true');
        $this->ssh->expects($this->once())->method('login')->willReturn(false);
        $this->security->expects($this->never())->method($this->anything());
        $this->logger->expects($this->once())->method('error');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertNull($coreData);
    }

    #[PU\Test]
    public function authenticateTimeout(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(false);
        $this->ssh->expects($this->once())->method('getServerPublicHostKey')->willReturn('host-pubkey-true');
        $this->ssh->expects($this->once())->method('login')->willThrowException(new TimeoutException());
        $this->security->expects($this->never())->method($this->anything());
        $this->logger->expects($this->once())->method('error');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertNull($coreData);
    }

    #[PU\Test]
    public function authenticateSuccess(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(false);
        $this->ssh->expects($this->once())->method('getServerPublicHostKey')->willReturn('host-pubkey-true');
        $this->ssh->expects($this->once())->method('login')->willReturn(true);
        $this->ssh->expects($this->once())->method('exec')->willReturn('Line1');
        $this->ssh->expects($this->once())->method('getExitStatus')->willReturn(0);
        $this->security->expects($this->once())->method('getUser')->willReturn(self::$mockUser);
        $this->logger->expects($this->once())->method('info');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertInstanceOf(CoreData::class, $coreData);
    }

    #[PU\Test]
    public function execParseOutputCorrectly(): void
    {
        $this->ssh->expects($this->once())->method('isAuthenticated')->willReturn(false);
        $this->ssh->expects($this->once())->method('getServerPublicHostKey')->willReturn('host-pubkey-true');
        $this->ssh->expects($this->once())->method('login')->willReturn(true);
        $this->ssh->expects($this->once())->method('exec')->willReturn("Line1\nLine2\nLine3");
        $this->ssh->expects($this->once())->method('getExitStatus')->willReturn(2);
        $this->security->expects($this->once())->method('getUser')->willReturn(self::$mockUser);
        $this->logger->expects($this->once())->method('info');

        $coreData = $this->coreExecSSH->exec('');

        $this->assertInstanceOf(CoreData::class, $coreData);
        $this->assertArraysAreEqual(['Line1', 'Line2', 'Line3'], $coreData->lineList);
        $this->assertSame(2, $coreData->exitCode);
    }
}
