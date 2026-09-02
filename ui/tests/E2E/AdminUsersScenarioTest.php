<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class AdminUsersScenarioTest extends E2EControllerTest
{
    #[PU\Test]
    public function adminUsersKeysRemove(): void
    {
        // Prepare core
        self::coreUserAdd(['', 'comment 1'], ['comment 2', '']);
        $user1Uuid = self::coreUserNumberToUuid(1);
        $user2Uuid = self::coreUserNumberToUuid(2);
        $user2Key1 = self::coreUserGenerateFakeKey(2, 1);
        $user2Key1Hash = md5($user2Key1);

        // Start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/admin/users');
        $this->waitForTurboframeLoaded('turboframe-admin-users-list');

        // Check title content, table header and cell content
        $this->assertPageTitleContains('admin.users.title');
        $this->assertSelectorTextContains('h1', 'admin.users.title');
        $this->assertAnySelectorTextContains('th', 'admin.users.list.uuid');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.key');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.dateAdded');
        $this->assertAnySelectorTextContains('th', 'user.keys.list.comment');
        $this->assertAnySelectorTextContains('td', $user1Uuid);
        $this->assertAnySelectorTextContains('td', $user2Uuid);

        // Click the expand button
        $this->clickElement('button-admin-users-expand-' . $user2Uuid);
        $this->waitForTurboframeReplace('turboframe-admin-users-keys-' . $user2Uuid);

        // Check sub-table content
        $this->assertAnySelectorTextNotContains('td', 'comment 1');
        $this->assertAnySelectorTextNotContains('td', 'AAAA1111');
        $this->assertAnySelectorTextContains('td', 'comment 2');
        $this->assertAnySelectorTextContains('td', 'AAAA2222');

        // Click the edit button of the second key
        $this->clickElement('button-admin-users-keys-edit-' . $user2Key1Hash);
        $this->waitForDiv('dropdown-admin-users-keys-edit-' . $user2Key1Hash);

        // Check dropdown content
        $this->assertAnySelectorTextContains('span', 'user.keys.list.remove');

        // Click the delete button
        $this->clickElement('button-admin-users-keys-remove-' . $user2Key1Hash);
        $this->waitForTurboframeLoaded('turboframe-user-keys-remove-' . $user2Key1Hash);

        // Check form label content
        $this->assertAnySelectorTextContains('div', 'user.keys.remove.label');

        // Submit form
        $this->submitForm('form-user-keys-remove-' . $user2Key1Hash);
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframeLoaded('turboframe-admin-users-list');

        // Check flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'user.keys.remove.success');
        $this->assertAnySelectorTextContains('td', $user1Uuid);
        $this->assertAnySelectorTextContains('td', $user2Uuid);

        // Click the expand button
        $this->clickElement('button-admin-users-expand-' . $user2Uuid);
        $this->waitForTurboframeReplace('turboframe-admin-users-keys-' . $user2Uuid);

        // Check sub-table content
        $this->assertAnySelectorTextNotContains('td', 'comment 2');
        $this->assertAnySelectorTextContains('td', 'AAAA2222');
    }
}
