<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class RepoControllerTest extends AbstractControllerTest
{
    #[PU\Test]
    public function repoIndex(): void
    {
        $this->coreExec->exec('repo create repo-1');

        $this->login(false);
        $this->client->request('GET', '/repo');

        $this->assertResponseIsSuccessful();
        $this->assertPageTitleContains('repo.title');
        $this->assertSelectorTextSame('h1', 'repo.title');
        $this->assertSelectorCount(1, 'turbo-frame[id="turboframe-repo-list"]');
    }

    #[PU\Test]
    #[PU\Depends('repoIndex')]
    public function repoList(): void
    {
        $this->login(false);
        $this->client->request('GET', '/repo/list', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, 'turbo-frame[id="turboframe-repo-list"]');
        $this->assertAnySelectorTextContains('th', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'git@localhost:repo-1.git');
    }

    #[PU\Test]
    #[PU\Depends('repoList')]
    public function repoCreate(): void
    {
        $this->login(false);
        $this->client->request('GET', '/repo/create', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, 'turbo-frame[id="turboframe-repo-create"]');
        $this->assertSelectorCount(1, 'form[action="/repo/create"]');

        $this->client->submitForm('submit', ['name' => 'repo@2'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('div', 'repo.create.invalid');

        $this->client->submitForm('submit', ['name' => 'repo-2'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    #[PU\Depends('repoCreate')]
    public function repoRename(): void
    {
        $this->login(false);
        $this->client->request('GET', '/repo/rename?old-name=repo-2', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, 'turbo-frame[id="turboframe-repo-rename-repo-2"]');
        $this->assertSelectorCount(1, 'form[action="/repo/rename"]');

        $this->client->submitForm('submit', ['old-name' => 'repo-1', 'new-name' => 'repo-3'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    #[PU\Depends('repoRename')]
    public function repoDelete(): void
    {
        $this->login(true);
        $this->client->request('GET', '/repo/delete?name=repo-3', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(1, 'turbo-frame[id="turboframe-repo-delete-repo-3"]');
        $this->assertSelectorCount(1, 'form[action="/repo/delete"]');

        $this->client->submitForm('submit', ['name' => 'repo-3'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    public function repoForbidden(): void
    {
        $this->login(false);
        $this->client->request('GET', '/repo/delete?name=repo-1', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseStatusCodeSame(403);
    }

    #[PU\Test]
    public function repoNoTurboframe(): void
    {
        $this->login(false);
        $this->client->request('GET', '/repo/list');

        $this->assertResponseRedirects('/repo');
    }
}
