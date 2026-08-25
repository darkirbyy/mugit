<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class HomeControllerTest extends AbstractControllerTest
{
    #[PU\Test]
    public function homeIndex(): void
    {
        $this->login(false);
        $this->client->request('GET', '/');

        $this->assertResponseRedirects('/repo');
    }

    #[PU\Test]
    public function homeSwitch(): void
    {
        $this->login(false);
        $this->client->request('GET', '/switch');

        $this->assertResponseRedirects('/');
        $this->assertTrue($this->client->getSession()->get('is-admin'));
    }
}
