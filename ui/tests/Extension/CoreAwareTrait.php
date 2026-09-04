<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use App\Service\CoreExecInterface;
use App\Tests\Mock\KeycloakMockEntryPoint;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

trait CoreAwareTrait
{
    protected static Finder $finder;
    protected static Filesystem $filesystem;
    protected static string $coreRootPath;
    protected static string $coreDataPath;
    protected static string $coreAuthorizedKeysFilepath;
    protected static string $coreLogFilepath;
    protected static CoreExecInterface $coreExec;

    public static function coreInitAndReset(): void
    {
        // Init static variables
        self::$finder = new Finder();
        self::$filesystem = new Filesystem();
        self::$coreRootPath = realpath(__DIR__ . '/../../../core');
        self::$coreDataPath = Path::join(self::$coreRootPath, $_ENV['CORE_DATA']);
        self::$coreAuthorizedKeysFilepath = Path::join(self::$coreDataPath, '.ssh', 'authorized_keys');
        self::$coreLogFilepath = Path::join(self::$coreDataPath, 'logs');
        self::$coreExec = self::getContainer()->get(CoreExecInterface::class);

        // Clear all git directories
        self::$finder->directories()->name('*.git')->depth('== 0')->in(self::$coreDataPath);
        if (self::$finder->hasResults()) {
            foreach (self::$finder as $file) {
                self::$filesystem->remove($file->getPathname());
            }
        }

        // Clear the authorized_keys and log files
        self::$filesystem->dumpFile(self::$coreAuthorizedKeysFilepath, '');
        self::$filesystem->dumpFile(self::$coreLogFilepath, '');
    }

    public static function coreRepoPath(string $repoName): string
    {
        return Path::join(self::$coreDataPath, $repoName . '.git');
    }

    public static function coreRepoAdd(string ...$repoNameList): void
    {
        foreach ($repoNameList as $repoName) {
            self::$filesystem->mkdir(self::coreRepoPath($repoName));
        }
    }

    public static function coreUserNumberToUuid(int $userNumber): string
    {
        return KeycloakMockEntryPoint::userNumberToUuid($userNumber);
    }

    public static function coreUserGenerateFakeKey(int $userNumber, int $keyNumber): string
    {
        return 'AAAA' . str_repeat((string) $userNumber, 32) . str_repeat((string) $keyNumber, 32);
    }

    public static function coreUserAdd(string|array|null ...$userCommentsList): void
    {
        $lines = [];
        foreach ($userCommentsList as $userNumber => $userComments) {
            $userUuid = self::coreUserNumberToUuid($userNumber + 1);
            foreach ((array) $userComments as $keyNumber => $userComment) {
                $key = self::coreUserGenerateFakeKey($userNumber + 1, $keyNumber + 1);
                $lines[] = 'ssh-ed25519 ' . $key . ' ' . $userUuid . ':' . time() . ':' . $userComment;
            }
        }
        self::$filesystem->appendToFile(self::$coreAuthorizedKeysFilepath, implode("\n", $lines) . "\n", true);
    }

    public static function coreAuthorizedKeysContent(): string
    {
        return self::$filesystem->readFile(self::$coreAuthorizedKeysFilepath);
    }

    public static function coreLogAdd(int $userNumber, string ...$logCommandList): void
    {
        $uuid = self::coreUserNumberToUuid($userNumber);
        $lines = [];
        foreach ($logCommandList as $logCommand) {
            $lines[] = time() . ' ' . $uuid . ' ' . $logCommand;
        }
        self::$filesystem->appendToFile(self::$coreLogFilepath, implode("\n", $lines) . "\n", true);
    }

    public static function coreLogContent(): string
    {
        return self::$filesystem->readFile(self::$coreLogFilepath);
    }
}
