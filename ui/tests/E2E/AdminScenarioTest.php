<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class AdminScenarioTest extends E2EControllerTest
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
        $this->assertPageTitleContains('user.list.title');
        $this->assertSelectorTextContains('h1', 'user.list.title');
        $this->assertAnySelectorTextContains('th', 'user.list.uuid');
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

    #[PU\Test]
    public function adminLogsNavigateThenPurge(): void
    {
        // Prepare core
        self::coreLogAdd(1, ...array_map(fn(int $i) => 'command ' . $i, range(1, 23)));

        // Start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/admin/logs');
        $this->waitForTurboframeLoaded('turboframe-admin-logs-list');

        // Check title content, table header and cell content
        $this->assertPageTitleContains('log.title');
        $this->assertSelectorTextContains('h1', 'log.title');
        $this->assertAnySelectorTextContains('th', 'log.list.date');
        $this->assertAnySelectorTextContains('th', 'log.list.uuid');
        $this->assertAnySelectorTextContains('th', 'log.list.command');
        $this->assertAnySelectorTextContains('td', 'command 1');
        $this->assertAnySelectorTextContains('td', 'command 10');

        // Click the next button
        $this->clickElement('button-admin-logs-page-next');
        $this->waitForTurboframeLoaded('turboframe-admin-logs-list');

        // Check table updated content
        $this->assertAnySelectorTextContains('td', 'command 11');
        $this->assertAnySelectorTextContains('td', 'command 20');

        // Click the purge button
        $this->clickElement('button-admin-logs-purge');
        $this->waitForDiv('dropdown-admin-logs-purge');
        $this->waitForTurboframeLoaded('turboframe-admin-logs-purge');

        // Check dropdown and label content
        $this->assertAnySelectorTextContains('span', 'log.list.purgeStart');
        $this->assertAnySelectorTextContains('div', 'log.purge.label');

        // Submit form
        $this->submitForm('form-admin-logs-purge');
        $this->waitForDiv('flash-success-main-0');
        $this->waitForTurboframeLoaded('turboframe-admin-logs-list');

        // Check flash content and table updated cell content
        $this->assertAnySelectorTextContains('div', 'log.purge.success');
        $this->assertAnySelectorTextContains('td', 'log.list.noLog');
        $this->assertAnySelectorTextNotContains('td', 'command 1');
        $this->assertAnySelectorTextNotContains('td', 'command 11');
        $this->assertAnySelectorTextNotContains('td', 'command 21');
    }
}
