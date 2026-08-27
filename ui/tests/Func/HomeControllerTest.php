<?php

declare(strict_types=1);

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes as PU;

final class HomeControllerTest extends FuncControllerTest
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
        $token = unserialize($this->client->getSession()->get('_security_main'));
        $this->assertTrue($token->getUser()->getIsAdmin());
    }
}
