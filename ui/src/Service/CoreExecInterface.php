<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreError;
use App\DTO\CoreOutput;

interface CoreExecInterface
{
    /**
     * Execute a command in the core API.
     *
     * @param string $command the command (see SPECIFICATION)
     */
    public function exec(string $command): CoreError|CoreOutput;
}
