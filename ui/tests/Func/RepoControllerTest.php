<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class RepoControllerTest extends FuncControllerTest
{
    #[PU\Test]
    public function repoIndex(): void
    {
        $this->login(false);

        $this->client->request('GET', '/repo');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');
    }

    #[PU\Test]
    public function repoList(): void
    {
        $this->login(false);
        self::coreRepoAdd('repo-1', 'repo-2');

        $this->client->request('GET', '/repo/list', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');
    }

    #[PU\Test]
    public function repoCreate(): void
    {
        $this->login(false);

        $this->client->request('GET', '/repo/create', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-create"]');
        $this->assertSelectorExists('form[action="/repo/create"]');

        $this->client->submitForm('form-repo-create-submit', ['name' => 'repo@1'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();

        $this->client->submitForm('form-repo-create-submit', ['name' => 'repo-1'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    public function repoRename(): void
    {
        $this->login(false);
        self::coreRepoAdd('repo-1');

        $this->client->request('GET', '/repo/rename?old-name=repo-1', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-rename-repo-1"]');
        $this->assertSelectorExists('form[action="/repo/rename"]');

        $this->client->submitForm('form-repo-rename-repo-1-submit', ['new-name' => 'repo-2'], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    public function repoDelete(): void
    {
        $this->login(true);
        self::coreRepoAdd('repo-1');

        $this->client->request('GET', '/repo/delete?name=repo-1', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-delete-repo-1"]');
        $this->assertSelectorExists('form[action="/repo/delete"]');

        $this->client->submitForm('form-repo-delete-repo-1-submit', [], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    public function repoForbidden(): void
    {
        $this->login(false);
        self::coreRepoAdd('repo-1');

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
