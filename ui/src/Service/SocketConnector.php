<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Connect to the core TCP socket.
 */
class SocketConnector
{
    public function __construct(
        #[Autowire('%core.addr%')] private string $coreAddr,
        #[Autowire('%core.socket_port%')] private int $coreSocketPort,
        #[Autowire('%core.connection_timeout%')] private int $coreConnectionTimeout,
    ) {}

    public function connect(): mixed
    {
        try {
            $socket = stream_socket_client('tcp://' . $this->coreAddr . ':' . $this->coreSocketPort, $errno, $errstr, $this->coreConnectionTimeout);
            if (!empty($errno)) {
                return false;
            }

            return $socket;
        } catch (\ErrorException $e) {
            return false;
        }
    }
}
