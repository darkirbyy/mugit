<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class RepoScenarioTest extends E2EControllerTest
{
    #[PU\Test]
    public function repoCreateFailThenSuccess(): void
    {
        // Start main request
        $this->client->request('GET', '/switch?is-admin=false');
        $this->client->request('GET', '/repo');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check title content, table header and cell content
        $this->assertPageTitleContains('repo.title');
        $this->assertSelectorTextContains('h1', 'repo.title');
        $this->assertAnySelectorTextContains('th', 'repo.list.name');
        $this->assertAnySelectorTextContains('th', 'repo.list.cloneURL');
        $this->assertAnySelectorTextContains('td', 'repo.list.noRepo');

        // Click the create button
        $this->clickElement('button-repo-create');
        $this->waitForDiv('dropdown-repo-create');
        $this->waitForTurboframe('turboframe-repo-create');

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'repo.list.createNew');
        $this->assertAnySelectorTextContains('label', 'repo.create.label');

        // Submit form with invalid name
        $this->submitForm('form-repo-create', ['name' => 'repo@1']);
        $this->waitForDiv('flash-error-repo-create-name');
        $this->waitForTurboframe('turboframe-repo-create');

        // Check error flash content
        $this->assertAnySelectorTextContains('div', 'repo.create.invalid');

        // Submit form with valid name
        $this->submitForm('form-repo-create', ['name' => 'repo-1']);
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check success flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'repo.create.success');
        $this->assertAnySelectorTextNotContains('td', 'repo.list.noRepo');
        $this->assertAnySelectorTextContains('td', 'repo-1');
    }

    #[PU\Test]
    public function repoRenameSuccess(): void
    {
        // Prepare core
        self::coreRepoAdd('repo-1');

        // Start main request
        $this->client->request('GET', '/switch?is-admin=false');
        $this->client->request('GET', '/repo');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check table cell content
        $this->assertAnySelectorTextContains('td', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'git@localhost:repo-1.git');

        // Click the edit button of one repo
        $this->clickElement('button-repo-edit-repo-1');
        $this->waitForDiv('dropdown-repo-edit-repo-1');

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'repo.list.rename');
        $this->assertAnySelectorTextNotContains('span', 'repo.list.delete');

        // Click the rename button
        $this->clickElement('button-repo-rename-repo-1');
        $this->waitForTurboframe('turboframe-repo-rename-repo-1');

        // Check form label content
        $this->assertAnySelectorTextContains('label', 'repo.rename.label');

        // Submit form with valid value
        $this->submitForm('form-repo-rename-repo-1', ['new-name' => 'repo-2']);
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'repo.rename.success');
        $this->assertAnySelectorTextContains('td', 'repo-2');
        $this->assertAnySelectorTextNotContains('td', 'repo-1');
    }

    #[PU\Test]
    public function repoDeleteSuccess(): void
    {
        // Prepare core
        self::coreRepoAdd('repo-1', 'repo-2');

        // Start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/repo');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check table header and cell content
        $this->assertAnySelectorTextContains('td', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'repo-2');

        // Click the edit button of one repo
        $this->clickElement('button-repo-edit-repo-1');
        $this->waitForDiv('dropdown-repo-edit-repo-1');

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'repo.list.rename');
        $this->assertAnySelectorTextContains('span', 'repo.list.delete');

        // Click the delete button
        $this->clickElement('button-repo-delete-repo-1');
        $this->waitForTurboframe('turboframe-repo-delete-repo-1');

        // Check form label content
        $this->assertAnySelectorTextContains('div', 'repo.delete.label');

        // Submit form
        $this->submitForm('form-repo-delete-repo-1');
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'repo.delete.success');
        $this->assertAnySelectorTextContains('td', 'repo-2');
        $this->assertAnySelectorTextNotContains('td', 'repo-1');
    }
}
