<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreOutput;
use Override;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SSH2;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreExecSSH implements CoreExecInterface
{
    private ?SSH2 $ssh;

    public function __construct(
        #[Autowire('%core.host_addr%')] private string $coreHostAddr,
        #[Autowire('%core.host_port%')] private int $coreHostPort,
        #[Autowire('%core.host_pubkey%')] private string $coreHostPubkey,
        #[Autowire('%core.root_prikey%')] private string $coreRootPrikey,
        private LoggerInterface $logger,
    ) {
        $this->ssh = null;
    }

    #[Override]
    public function exec(string $command): CoreOutput
    {
        $this->authenticate();

        $output = $this->ssh->exec('./api.sh ' . $command);
        $exitStatus = $this->ssh->getExitStatus();

        $lines = array_filter(explode("\n", $output));
        $exitCode = false !== $exitStatus ? $exitStatus : 1;

        return new CoreOutput($exitCode, $lines);
    }

    /**
     * Authenticate to the Core through SSH.
     * 
     * @throws UnableToConnectException when the fingerprint or the credentials are invalids
     * @return void
     */
    private function authenticate(): void
    {
        if (!is_null($this->ssh) && $this->ssh instanceof SSH2 && $this->ssh->isAuthenticated()) {
            $this->logger->info('Already authenticated to core through SSH.');

            return;
        }
        // todo don't throw exception and return false ?
        $this->ssh = new SSH2($this->coreHostAddr, $this->coreHostPort);
        if ($this->ssh->getServerPublicHostKey() != $this->coreHostPubkey) {
            throw new UnableToConnectException('Failed to connect to core through SSH : invalid public key.');
        }

        $key = PublicKeyLoader::load($this->coreRootPrikey);
        if (!$this->ssh->login('root', $key)) {
            throw new UnableToConnectException('Failed to authenticate to core through SSH : invalid user or private key.');
        }

        $this->logger->info('Successfully authenticated to core through SSH.');
    }
}
