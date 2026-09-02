<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class AdminUsersControllerTest extends FuncControllerTest
{
    #[PU\Test]
    public function adminUsersIndex(): void
    {
        $this->login(true);

        $this->client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-users-list"]');
    }

    #[PU\Test]
    public function adminUsersList(): void
    {
        $this->login(true);
        self::coreUserAdd('comment 1', 'comment 2');

        $this->client->request('GET', '/admin/users/list', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-users-list"]');
    }

    #[PU\Test]
    public function adminUsersKeys(): void
    {
        $this->login(true);
        self::coreUserAdd('comment 1', 'comment 2');

        $user1Uuid = self::coreUserNumberToUuid(1);
        $this->client->request('GET', '/admin/users/keys?uuid=' . $user1Uuid, [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-users-keys-' . $user1Uuid . '"]');
    }

    #[PU\Test]
    public function adminUsersKeysRemove(): void
    {
        $this->login(true);
        self::coreUserAdd('comment 1', 'comment 2');

        $user2Uuid = self::coreUserNumberToUuid(2);
        $user2Key1 = self::coreUserGenerateFakeKey(2, 1);
        $user2Key1Hash = md5($user2Key1);
        $this->client->request('GET', '/admin/users/keys/remove?uuid=' . $user2Uuid . '&key=' . $user2Key1, [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-user-keys-remove-' . $user2Key1Hash . '"]');
        $this->assertSelectorExists('form[action="/admin/users/keys/remove"]');

        $this->client->submitForm('form-user-keys-remove-' . $user2Key1Hash . '-submit', [], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/admin/users');
    }

    #[PU\Test]
    public function userKeysNoTurboframe(): void
    {
        $this->login(true);

        $this->client->request('GET', '/admin/users/list');

        $this->assertResponseRedirects('/admin/users');
    }

    #[PU\Test]
    #[PU\DataProvider('adminUsersNotAdminValues')]
    public function adminUsersNotAdmin(string $route): void
    {
        $this->login(false);

        $this->client->followRedirects(true);
        $this->client->request('GET', $route);

        $this->assertResponseStatusCodeSame(403);
    }

    public static function adminUsersNotAdminValues(): array
    {
        return [
            'index' => ['/admin/users'],
            'users list' => ['/admin/users/list'],
            'users keys' => ['/admin/users/keys'],
            'users keys remove' => ['/admin/users/keys/remove'],
        ];
    }
}
