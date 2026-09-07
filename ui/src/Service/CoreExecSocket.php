<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreData;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreExecSocket implements CoreExecInterface
{
    public function __construct(
        #[Autowire('%core.addr%')] private string $coreAddr,
        #[Autowire('%core.socket_port%')] private int $coreSocketPort,
        #[Autowire('%core.connection_timeout%')] private int $coreConnectionTimeout,
        private Security $security,
        private LoggerInterface $logger,
    ) {}

    #[\Override]
    public function exec(string $command): ?CoreData
    {
        try {
            $sock = stream_socket_client('tcp://' . $this->coreAddr . ':' . $this->coreSocketPort, $errno, $errstr, $this->coreConnectionTimeout);
            if (!empty($errno)) {
                $this->logger->error('Failed to connect to tcp socket:  ' . $errstr . '.');

                return null;
            }

            fwrite($sock, $this->security->getUser()->getId() . ' ' . $command . "\n");

            $response = stream_get_contents($sock);
            $reponseExploded = explode("\n", $response);
            $lineList = array_filter(array_slice($reponseExploded, 1), fn(string $line) => 0 !== strlen($line));
            $exitCode = (int) $reponseExploded[0];

            fclose($sock);

            return new CoreData($exitCode, $lineList);
        } catch (\ErrorException $e) {
            $this->logger->error('Failed to connect to tcp socket: ' . $e->getMessage() . '.');

            return null;
        }
    }
}
