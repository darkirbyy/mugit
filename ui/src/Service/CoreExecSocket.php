<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreData;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CoreExecSocket implements CoreExecInterface
{
    public function __construct(private SocketConnector $socketConnector, private Security $security, private LoggerInterface $logger) {}

    #[\Override]
    public function exec(string $command): ?CoreData
    {
        $socket = $this->socketConnector->connect();
        if (false === $socket) {
            $this->logger->error('Failed to connect to core TCP socket.');

            return null;
        }

        fwrite($socket, $this->security->getUser()->getId() . ' ' . $command . "\n");
        $response = stream_get_contents($socket);
        $reponseExploded = explode("\n", $response);
        if (0 === count($reponseExploded) || !ctype_digit($reponseExploded[0])) {
            $this->logger->error('Failed to get the exit code from the core response.');

            return null;
        }

        $lineList = array_filter(array_slice($reponseExploded, 1), fn(string $line) => 0 !== strlen($line));
        $exitCode = (int) $reponseExploded[0];

        fclose($socket);

        return new CoreData($exitCode, $lineList);
    }
}
