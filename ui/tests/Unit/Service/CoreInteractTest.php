<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\DTO\CoreData;
use App\DTO\ErrorData;
use App\DTO\RepoCreateData;
use App\DTO\RepoDeleteData;
use App\DTO\RepoListData;
use App\DTO\RepoRenameData;
use App\DTO\UserKeysAddData;
use App\DTO\UserKeysListData;
use App\DTO\UserKeysRemoveData;
use App\Service\CoreExecInterface;
use App\Service\CoreInteract;
use PHPUnit\Framework\Attributes as PU;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class CoreInteractTest extends TestCase
{
    private LoggerInterface $logger;
    private CoreExecInterface $coreExec;
    private CoreInteract $coreInteract;

    #[\Override]
    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->coreExec = $this->createMock(CoreExecInterface::class);
        $this->coreInteract = new CoreInteract($this->logger, $this->coreExec);
    }

    #[PU\Test]
    #[PU\DataProvider('coreValues')]
    public function coreError(string $coreMethod, mixed $input): void
    {
        $this->logger->expects($this->never())->method($this->anything());
        $this->coreExec->expects($this->once())->method('exec')->willReturn(null);

        $errorData = $this->coreInteract->$coreMethod($input);
        $this->assertInstanceOf(ErrorData::class, $errorData);
        $this->assertNotEmpty($errorData->textKey);
    }

    #[PU\Test]
    #[PU\DataProvider('coreValues')]
    public function nonZeroExitCode(string $coreMethod, mixed $input): void
    {
        $this->logger->expects($this->once())->method('error');
        $this->coreExec->expects($this->once())->method('exec')->willReturn(new CoreData(1, []));

        $errorData = $this->coreInteract->$coreMethod($input);
        $this->assertInstanceOf(ErrorData::class, $errorData);
        $this->assertNotEmpty($errorData->textKey);
    }

    #[PU\Test]
    #[PU\DataProvider('coreValues')]
    public function success(string $coreMethod, mixed $input): void
    {
        $this->logger->expects($this->never())->method($this->anything());
        $this->coreExec->expects($this->once())->method('exec')->willReturn(new CoreData(0, ['Line 1']));

        $errorData = $this->coreInteract->$coreMethod($input);
        $this->assertNull($errorData);
    }

    public static function coreValues(): array
    {
        return [
            'repo list' => ['repoList', new RepoListData()],
            'repo create' => ['repoCreate', new RepoCreateData('repo-1')],
            'repo rename' => ['repoRename', new RepoRenameData('repo-1', 'repo-2')],
            'repo delete' => ['repoDelete', new RepoDeleteData('repo-1')],
            'user keys list' => ['userKeysList', new UserKeysListData('uuid')],
            'user keys add' => ['userKeysAdd', new UserKeysAddData('uuid', 'key')],
            'user keys remove' => ['userKeysRemove', new UserKeysRemoveData('uuid', 'key')],
        ];
    }
}
