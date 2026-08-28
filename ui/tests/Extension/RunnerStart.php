<?php

declare(strict_types=1);

namespace App\Tests\Extension;

use PHPUnit\Event\TestRunner\Started;
use PHPUnit\Event\TestRunner\StartedSubscriber;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class RunnerStart implements StartedSubscriber
{
    public function __construct(private array $suiteNameList) {}

    public function notify(Started $event): void
    {
        $dotenv = new Dotenv();
        $filesystem = new Filesystem();
        $uiRootPath = realpath(__DIR__ . '/../../');
        $coreRootPath = realpath(__DIR__ . '/../../../core');

        // Load env variables
        if (method_exists(Dotenv::class, 'bootEnv')) {
            $dotenv->bootEnv(Path::join($uiRootPath, '.env'));
        }

        // Clear the cache if debug is set to false
        if (true === (bool) $_ENV['APP_DEBUG']) {
            umask(0000);
        } else {
            $filesystem->remove(Path::join($uiRootPath, 'var/cache/test'));
        }

        // FUNC and E2E ONLY : prepare a temporary public directory and compile the assets into it
        if (!empty(array_intersect($this->suiteNameList, TestsExtension::SUITE_REQUIRE_ASSETS))) {
            $pantherPublicPath = Path::join($uiRootPath, $_ENV['PANTHER_WEB_SERVER_DIR']);

            // Remove previous public directory
            $filesystem->remove($pantherPublicPath);
            $filesystem->mkdir($pantherPublicPath);

            // Create new one with valid entrypoint
            $index = $filesystem->readFile(Path::join($uiRootPath, 'public', 'index.php'));
            $index = str_replace('/vendor', '/../../vendor', $index);
            $filesystem->dumpFile(Path::join($pantherPublicPath, 'index.php'), $index);

            // Build the assets
            $process = new Process(['npm', 'run', 'test-build']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }

        // INTE, FUNC, E2E ONLY : start a test instance of the core
        if (!empty(array_intersect($this->suiteNameList, TestsExtension::SUITE_REQUIRE_CORE))) {
            $coreDataPath = Path::join($coreRootPath, $_ENV['CORE_DATA']);
            $coreKeysPath = Path::join($coreRootPath, $_ENV['CORE_KEYS']);

            // Creating test mounted directories is not exist
            $filesystem->remove($coreDataPath);
            $filesystem->mkdir($coreDataPath);
            $filesystem->mkdir($coreKeysPath);

            // Generating test keys if not exist
            $initKeysPath = Path::join($coreRootPath, 'init-keys.sh');
            $envTestLocalPath = Path::join($uiRootPath, '.env.test.local');
            $process = new Process([$initKeysPath, $_ENV['CORE_KEYS']]);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $output = preg_split('/###/', $process->getOutput(), 2);
            $filesystem->appendToFile($envTestLocalPath, count($output) > 1 ? '###' . $output[1] : '');

            // Reload env variables (because the previous script may have added some)
            if (method_exists(Dotenv::class, 'bootEnv')) {
                $dotenv->bootEnv(Path::join($uiRootPath, '.env'));
            }

            // Start the docker container
            $process = new Process(['docker', 'compose', '--project-directory', $coreRootPath, 'up', '-d']);
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        }
    }
}
