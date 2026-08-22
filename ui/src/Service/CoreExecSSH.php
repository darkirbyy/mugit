<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CoreErrorData;
use App\DTO\CoreOutputData;
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
        #[Autowire('%core.timeout%')] private int $coreTimeout,
        private LoggerInterface $logger,
    ) {
        $this->ssh = null;
    }

    #[\Override]
    public function exec(string $command): CoreErrorData|CoreOutputData
    {
        if (!$this->authenticate()) {
            return new CoreErrorData('repo.connectionFailed');
        }

        $output = $this->ssh->exec('./api.sh ' . $command);
        $exitStatus = $this->ssh->getExitStatus();

        $lineList = array_filter(explode("\n", $output));
        $exitCode = false !== $exitStatus ? $exitStatus : 1;

        return new CoreOutputData($exitCode, $lineList);
    }

    /**
     * Authenticate to the Core through SSH.
     *
     * @return bool true if authenticated successfully, false otherwise
     *
     * @throws UnableToConnectException when the fingerprint or the credentials are invalids
     */
    private function authenticate(): bool
    {
        if (!is_null($this->ssh) && $this->ssh instanceof SSH2 && $this->ssh->isAuthenticated()) {
            $this->logger->info('Already authenticated to core through SSH.');

            return true;
        }

        $this->ssh = new SSH2($this->coreHostAddr, $this->coreHostPort);
        $this->ssh->setTimeout($this->coreTimeout);
        try {
            if ($this->ssh->getServerPublicHostKey() != $this->coreHostPubkey) {
                $this->logger->error('Failed to connect to core through SSH: invalid public key.');

                return false;
            }

            $key = PublicKeyLoader::load($this->coreRootPrikey);
            if (!$this->ssh->login('root', $key)) {
                $this->logger->error('Failed to authenticate to core through SSH: invalid user or private key.');

                return false;
            }
        } catch (UnableToConnectException $e) {
            $this->logger->error('Failed to authenticate to core through SSH: encountered exception ' . $e::class . '.');

            return false;
        }

        $this->logger->info('Successfully authenticated to core through SSH.');

        return true;
    }
}
