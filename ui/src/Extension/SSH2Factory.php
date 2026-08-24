<?php

declare(strict_types=1);

namespace App\Extension;

use phpseclib4\Net\SSH2;

/**
 * Initialize a new SSH2 instance with the given parameters.
 */
class SSH2Factory
{
    public static function create(string $host, int $port, int $connectionTimeout): SSH2
    {
        return new SSH2($host, $port, $connectionTimeout);
    }
}
