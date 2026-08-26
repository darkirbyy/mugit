<?php

declare(strict_types=1);

namespace App\Tests\End2End;

use PHPUnit\Framework\Attributes as PU;

final class RepoScenarioTest extends End2EndControllerTest
{
    #[PU\Test]
    public function repoRenameSuccess(): void
    {
        $this->coreExec->exec('repo create repo-1');
        $this->client->request('GET', '/repo');
        $this->client->followRedirects(true);

        // Check title content, and presence of main list turbo-frame
        $this->assertPageTitleContains('repo.title');
        $this->assertSelectorTextContains('h1', 'repo.title');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->client->waitForAttributeToContain('turbo-frame[id="turboframe-repo-list"]', 'complete', 'true');

        // Check table header and cell content, and presence of the edit button of one repo
        $this->assertAnySelectorTextContains('th', 'repo.list.name');
        $this->assertAnySelectorTextContains('th', 'repo.list.cloneURL');
        $this->assertAnySelectorTextContains('td', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'git@localhost:repo-1.git');
        $this->assertSelectorExists('button[id="button-repo-edit-repo-1"]');

        // Click the edit button of one repo and wait for dropdown to appear
        $this->client->executeScript("document.querySelector('button[id=button-repo-edit-repo-1]').click()");
        $this->client->waitForVisibility('div[id="dropdown-repo-edit-repo-1"]');

        // Check dropdown button's content, and presence of the rename button
        $this->assertAnySelectorTextContains('div', 'repo.list.rename');
        $this->assertAnySelectorTextContains('div', 'repo.list.delete');
        $this->assertSelectorExists('button[id="button-repo-rename-repo-1"]');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-rename-repo-1"]');

        // Click the rename button wait for turbo-frame to be loaded
        $this->client->executeScript("document.querySelector('button[id=button-repo-rename-repo-1]').click()");
        $this->client->waitForAttributeToContain('turbo-frame[id="turboframe-repo-rename-repo-1"]', 'complete', 'true');

        // Check form label content, and presence of the input field and submit button
        $this->assertAnySelectorTextContains('label', 'repo.rename.label');
        $this->assertSelectorExists('input[id="form-repo-rename-repo-1-new-name"]');
        $this->assertSelectorExists('button[id="form-repo-rename-repo-1-submit"]');

        // Submit form and wait for flash to appear
        $this->client->submitForm('form-repo-rename-repo-1-submit', ['new-name' => 'repo-3'], 'POST', ['HTTP_Turbo_Frame' => 'true']);
        $this->client->waitForVisibility('div[id="flash-success-main-0"]');

        // Check flash content, and presence of main list turbo-frame
        $this->assertAnySelectorTextContains('div', 'repo.rename.success');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->client->waitForAttributeToContain('turbo-frame[id="turboframe-repo-list"]', 'complete', 'true');

        // Check table updated cell content
        $this->assertAnySelectorTextContains('td', 'repo-3');
        $this->assertAnySelectorTextNotContains('td', 'repo-1');
    }
}
