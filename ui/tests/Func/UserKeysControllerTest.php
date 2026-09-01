<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class UserKeysControllerTest extends FuncControllerTest
{
    #[PU\Test]
    public function userKeysIndex(): void
    {
        $this->login(false);

        $this->client->request('GET', '/user/keys');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-user-keys-list"]');
    }

    #[PU\Test]
    public function userKeysList(): void
    {
        $this->login(false);
        self::coreUserAdd('comment 1', 'comment 2');

        $this->client->request('GET', '/user/keys/list', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-user-keys-list"]');
    }

    #[PU\Test]
    public function userKeysAdd(): void
    {
        $this->login(false);
        self::coreUserAdd('comment 1', 'comment 2');

        $this->client->request('GET', '/user/keys/add', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-user-keys-add"]');
        $this->assertSelectorExists('form[action="/user/keys/add"]');

        $fullKeyInvalid = 'ssh-ed25519 ' . self::coreUserGenerateFakeKey(1, 1) . ' comment#2';
        $this->client->submitForm('form-user-keys-add-submit', ['full-key' => $fullKeyInvalid], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();

        $fullKeyValid = 'ssh-ed25519 ' . self::coreUserGenerateFakeKey(1, 2) . ' comment 2';
        $this->client->submitForm('form-user-keys-add-submit', ['full-key' => $fullKeyValid], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/user/keys');
    }

    #[PU\Test]
    public function userKeysRemove(): void
    {
        $this->login(true);
        self::coreUserAdd('comment 1', 'comment 2');

        $key = self::coreUserGenerateFakeKey(1, 1);
        $keyHash = md5($key);
        $this->client->request('GET', '/user/keys/remove?key=' . $key, [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-user-keys-remove-' . $keyHash . '"]');
        $this->assertSelectorExists('form[action="/user/keys/remove"]');

        $this->client->submitForm('form-user-keys-remove-' . $keyHash . '-submit', [], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/user/keys');
    }

    #[PU\Test]
    public function userKeysNoTurboframe(): void
    {
        $this->login(false);

        $this->client->request('GET', '/user/keys/list');

        $this->assertResponseRedirects('/user/keys');
    }
}
