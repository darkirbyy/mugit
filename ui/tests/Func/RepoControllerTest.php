<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;
use Symfony\Component\Panther\PantherTestCase;

final class RepoControllerTest extends PantherTestCase
{
    #[PU\Test]
    public function repoList(): void
    {
        $client = static::createPantherClient([]);
        $client->request('GET', '/repo');

        $this->assertPageTitleContains('Tous les dépôts');
        $this->assertSelectorTextContains('h1', 'Tous les dépôts');
    }
}
