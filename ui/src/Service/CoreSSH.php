<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\RepoInfo;
use Override;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Exception\UnableToConnectException;
use phpseclib3\Net\SSH2;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreSSH implements CoreInterface
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
    public function getRepoInfoList(): array
    {
        ['lines' => $lines, 'exitCode' => $exitCode] = $this->exec('repo list');

        if ($exitCode > 0) {
            throw new RuntimeException('TODO');
        }

        $repoInfoList = array_map(function ($line) {
            $lineExploded = explode(' ', $line);
            if (count($lineExploded) < 2) {
                throw new RuntimeException('TODO');
            }
            return new RepoInfo($lineExploded[0], (int)$lineExploded[1]);
        }, $lines);

        return $repoInfoList;
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

    /**
     * Execute a command through SSH, by connecting first if not already done.
     *
     * @param string $command   Command to execute (see the SPECIFICATION)
     * @return array            Array structure : ['output' => string[], 'exitCode' => int]
     */
    private function exec(string $command): array
    {
        $this->authenticate();

        $output = $this->ssh->exec('./api.sh ' . $command);
        $exitStatus = $this->ssh->getExitStatus();

        $lines = array_filter(explode("\n", $output));
        $exitCode = false !== $exitStatus ? $exitStatus : 1;

        return ['lines' => $lines, 'exitCode' => $exitCode];
    }
}
