<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;
use Symfony\Component\Panther\PantherTestCase;

final class RepoScenarioTest extends PantherTestCase
{
    #[PU\Test]
    public function repoList(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/repo');

        $this->assertPageTitleContains('repo.title');
        $this->assertSelectorTextContains('h1', 'repo.title');
    }
}
