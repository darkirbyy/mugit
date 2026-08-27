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
        $this->client->request('GET', '/repo');
        $this->client->followRedirects(true);

        // Check title content, and presence of main list turbo-frame
        $this->assertPageTitleContains('repo.title');
        $this->assertSelectorTextContains('h1', 'repo.title');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table header and cell content, and presence of the create button
        $this->assertAnySelectorTextContains('th', 'repo.list.name');
        $this->assertAnySelectorTextContains('th', 'repo.list.cloneURL');
        $this->assertAnySelectorTextContains('td', 'repo.list.noRepo');
        $this->assertSelectorExists('button[id="button-repo-create"]');

        // Click the create button of one repo and wait for dropdown to appear and turbo-frame to be loaded
        $this->clickButton('button-repo-create');
        $this->waitForDiv('dropdown-repo-create');
        $this->waitForTurboframeLoaded('turboframe-repo-create');

        // Check dropdown button's content, and presence of the rename button
        $this->assertAnySelectorTextContains('div', 'repo.list.createNew');
        $this->assertAnySelectorTextContains('label', 'repo.create.label');
        $this->assertSelectorExists('input[id="form-repo-create-name"]');
        $this->assertSelectorExists('button[id="form-repo-create-submit"]');

        // Submit form with invalid name and wait for error flash to appear
        $this->submitForm('form-repo-create-submit', ['name' => 'repo@1']);
        $this->waitForDiv('flash-error-repo-create-name');

        // Check error flash content, and presence of main list turbo-frame
        $this->assertAnySelectorTextContains('div', 'repo.create.invalid');

        // Submit form with valid name and wait for success flash to appear
        $this->submitForm('form-repo-create-submit', ['name' => 'repo-1']);
        $this->waitForDiv('flash-success-main-0');

        // Check success flash content, and presence of main list turbo-frame
        $this->assertAnySelectorTextContains('div', 'repo.create.success');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table updated cell content
        $this->assertAnySelectorTextNotContains('td', 'repo.list.noRepo');
        $this->assertAnySelectorTextContains('td', 'repo-1');
    }

    #[PU\Test]
    public function repoRenameSuccess(): void
    {
        // Prepare core
        self::coreRepoAdd('repo-1');

        // Start main request
        $this->client->request('GET', '/repo');
        $this->client->followRedirects(true);

        // Check presence of main list turbo-frame
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table header and cell content, and presence of the edit button of one repo
        $this->assertAnySelectorTextContains('td', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'git@localhost:repo-1.git');
        $this->assertSelectorExists('button[id="button-repo-edit-repo-1"]');

        // Click the edit button of one repo and wait for dropdown to appear
        $this->clickButton('button-repo-edit-repo-1');
        $this->waitForDiv('dropdown-repo-edit-repo-1');

        // Check dropdown button's content, and presence of the rename button
        $this->assertAnySelectorTextContains('div', 'repo.list.rename');
        $this->assertAnySelectorTextNotContains('div', 'repo.list.delete');
        $this->assertSelectorExists('button[id="button-repo-rename-repo-1"]');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-rename-repo-1"]');

        // Click the rename button wait for turbo-frame to be loaded
        $this->clickButton('button-repo-rename-repo-1');
        $this->waitForTurboframeLoaded('turboframe-repo-rename-repo-1');

        // Check form label content, and presence of the input field and submit button
        $this->assertAnySelectorTextContains('label', 'repo.rename.label');
        $this->assertSelectorExists('input[id="form-repo-rename-repo-1-new-name"]');
        $this->assertSelectorExists('button[id="form-repo-rename-repo-1-submit"]');

        // Submit form and wait for flash to appear
        $this->submitForm('form-repo-rename-repo-1-submit', ['new-name' => 'repo-2']);
        $this->waitForDiv('flash-success-main-0');

        // Check flash content, and presence of main list turbo-frame
        $this->assertAnySelectorTextContains('div', 'repo.rename.success');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table updated cell content
        $this->assertAnySelectorTextContains('td', 'repo-2');
        $this->assertAnySelectorTextNotContains('td', 'repo-1');
    }

    #[PU\Test]
    public function repoDeleteSuccess(): void
    {
        // Prepare core
        self::coreRepoAdd('repo-1', 'repo-2');

        // Switch to amdin and start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/repo');
        $this->client->followRedirects(true);

        // Check presence of main list turbo-frame
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table header and cell content, and presence of the edit button of one repo
        $this->assertAnySelectorTextContains('td', 'repo-1');
        $this->assertAnySelectorTextContains('td', 'repo-2');
        $this->assertSelectorExists('button[id="button-repo-edit-repo-1"]');

        // Click the edit button of one repo and wait for dropdown to appear
        $this->clickButton('button-repo-edit-repo-1');
        $this->waitForDiv('dropdown-repo-edit-repo-1');

        // Check dropdown button's content, and presence of the delete button
        $this->assertAnySelectorTextContains('div', 'repo.list.rename');
        $this->assertAnySelectorTextContains('div', 'repo.list.delete');
        $this->assertSelectorExists('button[id="button-repo-delete-repo-1"]');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-delete-repo-1"]');

        // Click the delete button wait for turbo-frame to be loaded
        $this->clickButton('button-repo-delete-repo-1');
        $this->waitForTurboframeLoaded('turboframe-repo-delete-repo-1');

        // Check form label content, and presence of the input field and submit button
        $this->assertAnySelectorTextContains('div', 'repo.delete.label');
        $this->assertSelectorExists('button[id="form-repo-delete-repo-1-submit"]');

        // Submit form and wait for flash to appear
        $this->submitForm('form-repo-delete-repo-1-submit');
        $this->waitForDiv('flash-success-main-0');

        // Check flash content, and presence of main list turbo-frame
        $this->assertAnySelectorTextContains('div', 'repo.delete.success');
        $this->assertSelectorExists('turbo-frame[id="turboframe-repo-list"]');

        // Wait for main list turbo-frame to be loaded
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check table updated cell content
        $this->assertAnySelectorTextContains('td', 'repo-2');
        $this->assertAnySelectorTextNotContains('td', 'repo-1');
    }
}
