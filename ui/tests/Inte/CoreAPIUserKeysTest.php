<?php

declare(strict_types=1);

namespace App\Tests\Inte;

use App\Tests\Extension\CoreAwareTrait;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class CoreAPIUserKeysTest extends KernelTestCase
{
    use CoreAwareTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::coreInit();
    }

    #[\Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::coreReset();
    }

    #[PU\Test]
    public function userList(): void
    {
        self::coreUserAdd('comment 1', 'comment 2');
        self::coreUserAdd(''); // double call to place the second key of user 1 after and check the sort + uniq combination

        $coreData = self::$coreExec->exec('user list');

        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount(2, $coreData->lineList);
        $this->assertSame(self::coreUserNumberToUuid(1), $coreData->lineList[0]);
        $this->assertSame(self::coreUserNumberToUuid(2), $coreData->lineList[1]);
    }

    #[PU\Test]
    public function userKeysList(): void
    {
        self::coreUserAdd(['comment 1', '']);

        $user1Uuid = self::coreUserNumberToUuid(1);
        $coreData = self::$coreExec->exec('user key-list ' . $user1Uuid);

        $this->assertSame(0, $coreData->exitCode);
        $this->assertCount(2, $coreData->lineList);
        $this->assertStringStartsWith('AAAA', $coreData->lineList[0]);
        $this->assertStringEndsWith(' comment 1', $coreData->lineList[0]);
        $this->assertStringStartsWith('AAAA', $coreData->lineList[1]);
        $this->assertStringEndsWith(' ', $coreData->lineList[1]);
    }

    #[PU\Test]
    public function userKeysAdd(): void
    {
        self::coreUserAdd(null, 'comment 2');

        $user1Uuid = self::coreUserNumberToUuid(1);
        $user2Uuid = self::coreUserNumberToUuid(2);
        $user1Key1 = self::coreUserGenerateFakeKey(1, 1);
        $user2Key1 = self::coreUserGenerateFakeKey(2, 1);
        $coreData = self::$coreExec->exec('user key-add ' . $user1Uuid . ' \'' . $user1Key1 . '\' \'comment 1\'');

        $authorizedKeysContent = self::coreAuthorizedKeysContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertSame(2, substr_count($authorizedKeysContent, "\n"));
        $this->assertStringContainsString('ssh-ed25519 ' . $user1Key1 . ' ' . $user1Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('comment 1', $authorizedKeysContent);
        $this->assertStringContainsString('ssh-ed25519 ' . $user2Key1 . ' ' . $user2Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('comment 2', $authorizedKeysContent);
    }

    #[PU\Test]
    public function userKeysRemove(): void
    {
        self::coreUserAdd(['comment 1', ''], 'comment 2');

        $user1Uuid = self::coreUserNumberToUuid(1);
        $user2Uuid = self::coreUserNumberToUuid(2);
        $user1Key1 = self::coreUserGenerateFakeKey(1, 1);
        $user1Key2 = self::coreUserGenerateFakeKey(1, 2);
        $user2Key1 = self::coreUserGenerateFakeKey(2, 1);
        $coreData = self::$coreExec->exec('user key-remove ' . $user1Uuid . ' \'' . $user1Key1 . '\'');

        $authorizedKeysContent = self::coreAuthorizedKeysContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertSame(2, substr_count($authorizedKeysContent, "\n"));
        $this->assertStringNotContainsString('ssh-ed25519 ' . $user1Key1 . ' ' . $user1Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('ssh-ed25519 ' . $user1Key2 . ' ' . $user1Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('ssh-ed25519 ' . $user2Key1 . ' ' . $user2Uuid . ':', $authorizedKeysContent);
        $this->assertStringNotContainsString('comment 3', $authorizedKeysContent);
    }

    #[PU\Test]
    public function userDelete(): void
    {
        self::coreUserAdd(['comment 1', ''], 'comment 2');

        $user1Uuid = self::coreUserNumberToUuid(1);
        $user2Uuid = self::coreUserNumberToUuid(2);
        $user1Key1 = self::coreUserGenerateFakeKey(1, 1);
        $user1Key2 = self::coreUserGenerateFakeKey(1, 2);
        $user2Key1 = self::coreUserGenerateFakeKey(2, 1);
        $coreData = self::$coreExec->exec('user delete ' . $user1Uuid);

        $authorizedKeysContent = self::coreAuthorizedKeysContent();
        $this->assertSame(0, $coreData->exitCode);
        $this->assertSame(1, substr_count($authorizedKeysContent, "\n"));
        $this->assertStringNotContainsString('ssh-ed25519 ' . $user1Key1 . ' ' . $user1Uuid . ':', $authorizedKeysContent);
        $this->assertStringNotContainsString('ssh-ed25519 ' . $user1Key2 . ' ' . $user1Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('ssh-ed25519 ' . $user2Key1 . ' ' . $user2Uuid . ':', $authorizedKeysContent);
        $this->assertStringContainsString('comment 2', $authorizedKeysContent);
    }

    #[PU\Test]
    #[PU\DataProvider('userFailValues')]
    public function userFail(string $subcommand, int $expectedExitCode): void
    {
        self::coreUserAdd(['comment 1', ''], 'comment 2');

        $coreData = self::$coreExec->exec('user ' . $subcommand);

        $authorizedKeysContent = self::coreAuthorizedKeysContent();
        $this->assertSame($expectedExitCode, $coreData->exitCode);
        $this->assertSame(3, substr_count($authorizedKeysContent, "\n"));
    }

    public static function userFailValues(): array
    {
        $user1Uuid = self::coreUserNumberToUuid(1);
        $user1Key2 = self::coreUserGenerateFakeKey(1, 2);
        $user1Key3 = self::coreUserGenerateFakeKey(1, 3);

        return [
            'missing subcommand' => ['', 1],
            'invalid subcommand' => ['key-delete', 2],
            'key-list, empty uuid' => ['key-list', 3],
            'key-list, invalid uuid' => ['key-list 1111-1111-1111', 4],
            'key-add, empty key' => ['key-add ' . $user1Uuid, 3],
            'key-add, invalid key' => ['key-add ' . $user1Uuid . 'AAAAtest', 4],
            'key-add, already exist key' => ['key-add ' . $user1Uuid . ' \'' . $user1Key2 . '\' \'comment 3\'', 9],
            'key-remove, empty key' => ['key-remove ' . $user1Uuid, 3],
            'key-remove, invalid key' => ['key-remove ' . $user1Uuid . '\'AAAAtest\'', 4],
            'key-remove, does not exist key' => ['key-remove ' . $user1Uuid . ' \'' . $user1Key3 . '\'', 8],
            'user delete, empty uuid' => ['delete', 3],
            'user delete, invalid uuid' => ['delete 1111-1111-1111', 4],
        ];
    }
}
