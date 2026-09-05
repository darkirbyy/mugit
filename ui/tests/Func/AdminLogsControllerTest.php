<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class AdminLogsControllerTest extends FuncControllerTest
{
    #[PU\Test]
    public function adminLogsIndex(): void
    {
        $this->login(true);

        $this->client->request('GET', '/admin/logs');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-logs-list"]');
    }

    #[PU\Test]
    public function adminLogsList(): void
    {
        $this->login(true);
        self::coreLogAdd(1, 'command 1', 'command 2');

        $this->client->request('GET', '/admin/logs/list', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-logs-list"]');
    }

    #[PU\Test]
    public function adminLogsPurge(): void
    {
        $this->login(true);
        self::coreLogAdd(1, 'command 1', 'command 2');

        $this->client->request('GET', '/admin/logs/purge', [], [], ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('turbo-frame[id="turboframe-admin-logs-purge"]');
        $this->assertSelectorExists('form[action="/admin/logs/purge"]');

        $this->client->submitForm('form-admin-logs-purge-submit', [], 'POST', ['HTTP_Turbo_Frame' => 'true']);

        $this->assertResponseRedirects('/admin/logs');
    }

    #[PU\Test]
    public function adminLogsNoTurboframe(): void
    {
        $this->login(true);

        $this->client->request('GET', '/admin/logs/list');

        $this->assertResponseRedirects('/admin/logs');
    }

    #[PU\Test]
    #[PU\DataProvider('adminLogsNotAdminValues')]
    public function adminLogsNotAdmin(string $route): void
    {
        $this->login(false);

        $this->client->followRedirects(true);
        $this->client->request('GET', $route);

        $this->assertResponseStatusCodeSame(403);
    }

    public static function adminLogsNotAdminValues(): array
    {
        return [
            'index' => ['/admin/logs'],
            'logs list' => ['/admin/logs/list'],
            'logs purge' => ['/admin/logs/purge'],
        ];
    }
}
