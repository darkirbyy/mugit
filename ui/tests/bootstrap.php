<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

require dirname(__DIR__) . '/vendor/autoload.php';
$coreRootPath = realpath(__DIR__ . '/../../core');

// Load env variables
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// Creating test mounted directories is not exist
(new Filesystem())->mkdir(Path::join($coreRootPath, $_ENV['CORE_DATA']));
(new Filesystem())->mkdir(Path::join($coreRootPath, $_ENV['CORE_KEYS']));

// Generating test keys if not exist
passthru(__DIR__ . '/../../core/init-keys.sh "' . $_ENV['CORE_KEYS'] . '" | sed -n \'/###/,$p\' >> "' . __DIR__ . '/../.env.test.local"');

// Reload env variables (because the previous script may have added some)
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// Clear the cache if debug is set to false
if (true === (bool) $_SERVER['APP_DEBUG']) {
    umask(0000);
} else {
    (new Filesystem())->remove(__DIR__ . '/../var/cache/test');
}
