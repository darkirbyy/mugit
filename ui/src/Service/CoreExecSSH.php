<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreData;
use phpseclib4\Crypt\PublicKeyLoader;
use phpseclib4\Exception\BaseException;
use phpseclib4\Net\SSH2;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreExecSSH implements CoreExecInterface
{
    public function __construct(
        #[Autowire('%core.host_pubkey%')] private string $coreHostPubkey,
        #[Autowire('%core.root_prikey%')] private string $coreRootPrikey,
        private SSH2 $ssh,
        private LoggerInterface $logger,
    ) {}

    #[\Override]
    public function exec(string $command): ?CoreData
    {
        if (!$this->authenticate()) {
            return null;
        }

        $output = $this->ssh->exec('./api.sh ' . $command);
        $exitStatus = $this->ssh->getExitStatus();

        $lineList = array_filter(explode("\n", $output));
        $exitCode = false !== $exitStatus ? $exitStatus : 1;

        return new CoreData($exitCode, $lineList);
    }

    /**
     * Authenticate to the Core through SSH.
     *
     * @return bool true if authenticated successfully, false otherwise
     *
     * @throws BaseException when the fingerprint or the credentials are invalids
     */
    private function authenticate(): bool
    {
        if ($this->ssh->isAuthenticated()) {
            $this->logger->info('Already authenticated to core through SSH.');

            return true;
        }

        try {
            if ($this->ssh->getServerPublicHostKey() != $this->coreHostPubkey) {
                $this->logger->error('Failed to connect to core through SSH: invalid public key.');

                return false;
            }

            $key = PublicKeyLoader::loadPrivateKey($this->coreRootPrikey);
            if (!$this->ssh->login('root', $key)) {
                $this->logger->error('Failed to authenticate to core through SSH: invalid user or private key.');

                return false;
            }
        } catch (BaseException $e) {
            $this->logger->error('Failed to authenticate to core through SSH: encountered exception ' . $e::class . '.');

            return false;
        }

        $this->logger->info('Successfully authenticated to core through SSH.');

        return true;
    }
}
