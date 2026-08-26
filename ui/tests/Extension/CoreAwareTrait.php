<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use App\Service\CoreExecInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

trait CoreAwareTrait
{
    private static CoreExecInterface $coreExec;
    private static string $coreRootPath;
    private static string $coreDataPath;

    public static function coreInit(): void
    {
        // todo : better way to find the core data path ?
        self::$coreRootPath = Path::join(__DIR__, '..', '..', '..', 'core');
        self::$coreDataPath = Path::join(self::$coreRootPath, $_ENV['CORE_DATA']);
        self::$coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    public static function coreRepoAdd(string ...$repoNameList): void
    {
        $filesystem = new Filesystem();
        foreach ($repoNameList as $repoName) {
            $filesystem->mkdir(Path::join(self::$coreDataPath, $repoName . '.git'));
        }
    }

    public static function coreRepoPath(string $repoName): string
    {
        return Path::join(self::$coreDataPath, $repoName . '.git');
    }

    public static function coreReset(): void
    {
        $finder = new Finder();
        $filesystem = new Filesystem();

        $finder->directories()->name('*.git')->depth('== 0')->in(self::$coreDataPath);
        if (!$finder->hasResults()) {
            return;
        }

        foreach ($finder as $file) {
            $filesystem->remove($file->getPathname());
        }
    }
}
