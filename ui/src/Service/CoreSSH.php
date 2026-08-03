<?php

declare(strict_types=1);

namespace App\Service;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SSH2;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoreSSH
{
    private ?SSH2 $ssh;

    public function __construct(
        #[Autowire('%app.core_host_addr%')] private string $coreHostAddr,
        #[Autowire('%app.core_host_port%')] private int $coreHostPort,
        #[Autowire('%app.core_host_pubkey%')] private string $coreHostPubkey,
        #[Autowire('%app.core_api_user%')] private string $coreApiUser,
        #[Autowire('%app.core_api_key%')] private string $coreApiKey,
        private LoggerInterface $logger,
    ) {
        $this->ssh = null;
    }

    private function authenticate(): bool
    {
        if (!is_null($this->ssh) && $this->ssh instanceof SSH2 && $this->ssh->isAuthenticated()) {
            $this->logger->info('Already authenticated to core through SSH.');
            return true;
        }

        $this->ssh = new SSH2($this->coreHostAddr, $this->coreHostPort);
        if ($this->ssh->getServerPublicHostKey() != $this->coreHostPubkey) {
            $this->logger->warning('Failed to connect to core through SSH : invalid public key.');
            return false;
        }

        $key = PublicKeyLoader::load($this->coreApiKey);
        if (!$this->ssh->login($this->coreApiUser, $key)) {
            $this->logger->warning('Failed to authenticate to core through SSH : invalid user or private key.');
            return false;
        }

        $this->ssh->enableQuietMode();

        return true;
    }

    public function exec(string $command): array
    {
        if(!$this->authenticate()){
            return ['output' => [], 'exitCode' => 1] ;
        }

        $stdout = $this->ssh->exec('api ' . $command);
        $exitStatus = $this->ssh->getExitStatus();

        $output = array_filter(explode("\n", $stdout));
        $exitCode = $exitStatus !== false ? $exitStatus : 1;

        return ['output' => $output, 'exitCode' => $exitCode] ;
    }
}
