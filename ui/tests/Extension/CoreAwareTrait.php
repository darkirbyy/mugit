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
    private static string $coreRootPath;
    private static string $coreDataPath;
    private static string $coreAuthorizedKeysFilepath;
    private static CoreExecInterface $coreExec;

    public static function coreInit(): void
    {
        self::$coreRootPath = realpath(__DIR__ . '/../../../core');
        self::$coreDataPath = Path::join(self::$coreRootPath, $_ENV['CORE_DATA']);
        self::$coreAuthorizedKeysFilepath = Path::join(self::$coreDataPath, '.ssh', 'authorized_keys');
        self::$coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    public static function coreRepoPath(string $repoName): string
    {
        return Path::join(self::$coreDataPath, $repoName . '.git');
    }

    public static function coreRepoAdd(string ...$repoNameList): array
    {
        $filesystem = new Filesystem();
        $paths = [];
        foreach ($repoNameList as $repoName) {
            $repoPath = self::coreRepoPath($repoName);
            $filesystem->mkdir($repoPath);
            $paths[] = $repoPath;
        }

        return $paths;
    }

    public static function coreUserGenerateFakeKey(int $userNumber, int $keyNumber): string
    {
        return 'AAAA' . str_repeat((string) $userNumber, 32) . str_repeat((string) $keyNumber, 32);
    }

    public static function coreUserAdd(string|array|null ...$userCommentsList): array
    {
        $filesystem = new Filesystem();
        $lines = [];
        $keys = [];
        foreach ($userCommentsList as $userNumber => $userComments) {
            $userUuid = KeycloakMockEntryPoint::userNumberToUuid($userNumber + 1);
            $keys[$userUuid] = [];
            foreach ((array) $userComments as $keyNumber => $userComment) {
                $key = self::coreUserGenerateFakeKey($userNumber + 1, $keyNumber + 1);
                $lines[] = 'ssh-ed25519 ' . $key . ' ' . $userUuid . ':' . time() . ':' . $userComment;
                $keys[$userUuid][] = $key;
            }
        }
        $filesystem->appendToFile(self::$coreAuthorizedKeysFilepath, implode("\n", $lines) . "\n", true);

        return $keys;
    }

    public static function coreAuthorizedKeysContent(): string
    {
        $filesystem = new Filesystem();

        return $filesystem->readFile(self::$coreAuthorizedKeysFilepath);
    }

    public static function coreReset(): void
    {
        $finder = new Finder();
        $filesystem = new Filesystem();

        // Clear all git directories
        $finder->directories()->name('*.git')->depth('== 0')->in(self::$coreDataPath);
        if ($finder->hasResults()) {
            foreach ($finder as $file) {
                $filesystem->remove($file->getPathname());
            }
        }

        // Clear the authorized_keys file
        $filesystem->dumpFile(self::$coreAuthorizedKeysFilepath, '');
    }
}
