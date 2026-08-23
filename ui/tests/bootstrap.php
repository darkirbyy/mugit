<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__) . '/vendor/autoload.php';
$coreRootPath = __DIR__ . '/../../core';

// Load env variables
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

// Purging previous test data directory
(new Filesystem())->remove($coreRootPath . '/' . $_ENV['CORE_DATA']);
(new Filesystem())->mkdir($coreRootPath . '/' . $_ENV['CORE_DATA']);

// Init test keys if not already exist
(new Filesystem())->mkdir($coreRootPath . '/' . $_ENV['CORE_KEYS']);
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
