<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class UserScenarioTest extends E2EControllerTest
{
    #[PU\Test]
    public function userKeysAddFailThenSuccess(): void
    {
        // Start main request
        $this->client->request('GET', '/switch?is-admin=false');
        $this->client->request('GET', '/user/keys');
        $this->waitForTurboframeLoaded('turboframe-user-keys-list');

        // Check title content, table header and cell content
        $this->assertPageTitleContains('user.keys.title');
        $this->assertSelectorTextContains('h1', 'user.keys.title');
        $this->assertAnySelectorTextContains('div', 'user.keys.help');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.key');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.dateAdded');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.comment');
        $this->assertAnySelectorTextContains('td', 'user.keys.list.noKey');

        // Click the create button
        $this->clickElement('button-user-keys-add');
        $this->waitForDiv('dropdown-user-keys-add');
        $this->waitForTurboframeLoaded('turboframe-user-keys-add');

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'user.keys.list.addNew');
        $this->assertAnySelectorTextContains('label', 'user.keys.add.label');

        // Submit form with invalid name
        $fullKeyInvalid = 'ssh-ed25519 ' . self::coreUserGenerateFakeKey(1, 1) . ' comment(3)';
        $this->submitForm('form-user-keys-add', ['full-key' => $fullKeyInvalid]);
        $this->waitForDiv('flash-error-user-keys-add-full-key');
        $this->waitForTurboframeLoaded('turboframe-user-keys-add');

        // Check error flash content
        $this->assertAnySelectorTextContains('div', 'user.keys.add.invalid');

        // Submit form with valid name
        $fullKeyValid = 'ssh-ed25519 ' . self::coreUserGenerateFakeKey(1, 1) . ' comment 3';
        $this->submitForm('form-user-keys-add', ['full-key' => $fullKeyValid]);
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframeLoaded('turboframe-user-keys-list');

        // Check success flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'user.keys.add.success');
        $this->assertAnySelectorTextNotContains('td', 'user.keys.list.noKey');
        $this->assertAnySelectorTextContains('td', 'AAAA1111');
        $this->assertAnySelectorTextContains('td', 'comment 3');
    }

    #[PU\Test]
    public function userKeysDeleteSuccess(): void
    {
        // Prepare core
        self::coreUserAdd(['comment 1', 'comment 2'], 'comment 3');

        // Start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/user/keys');
        $this->waitForTurboframeLoaded('turboframe-user-keys-list');

        // Check table header and cell content
        $this->assertAnySelectorTextContains('td', 'AAAA1111');
        $this->assertAnySelectorTextContains('td', 'comment 1');
        $this->assertAnySelectorTextContains('td', 'comment 2');
        $this->assertAnySelectorTextNotContains('td', 'AAAA2222');
        $this->assertAnySelectorTextNotContains('td', 'comment 3');

        // Click the edit button of one repo
        $key = self::coreUserGenerateFakeKey(1, 1);
        $keyHash = md5($key);
        $this->clickElement('button-user-keys-edit-' . $keyHash);
        $this->waitForDiv('dropdown-user-keys-edit-' . $keyHash);

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'user.keys.list.remove');

        // Click the remove button
        $this->clickElement('button-user-keys-remove-' . $keyHash);
        $this->waitForTurboframeLoaded('turboframe-user-keys-remove-' . $keyHash);

        // Check form label content
        $this->assertAnySelectorTextContains('div', 'user.keys.remove.label');

        // Submit form
        $this->submitForm('form-user-keys-remove-' . $keyHash);
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframeLoaded('turboframe-user-keys-list');

        // Check flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'user.keys.remove.success');
        $this->assertAnySelectorTextContains('td', 'AAAA1111');
        $this->assertAnySelectorTextContains('td', 'comment 2');
        $this->assertAnySelectorTextNotContains('td', 'comment 1');
    }
}
