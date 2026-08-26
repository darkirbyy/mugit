<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\Extension\CoreAwareTrait;
use Override;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class E2EControllerTest extends PantherTestCase
{
    use CoreAwareTrait;

    protected Client $client;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createPantherClient();
        self::coreInit();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        self::coreReset();
    }

    public function switchToAdmin(): void
    {
        $this->client->waitForVisibility('a[id="link-switch-role"]');
        $this->clickLink('link-switch-role');
        $this->client->waitForVisibility('a[id="link-switch-role"]');
    }

    public function clickLink(string $linkId): void
    {
        $this->client->executeScript("document.querySelector('a[id=" . $linkId . "]').click()");
    }

    public function clickButton(string $buttonId): void
    {
        $this->client->executeScript("document.querySelector('button[id=" . $buttonId . "]').click()");
    }

    public function submitForm(string $buttonId, array $values = []): void
    {
        $this->client->submitForm($buttonId, $values, 'POST', ['HTTP_Turbo_Frame' => 'true']);
    }

    public function waitForTurboframeLoaded(string $turboframeId): void
    {
        $this->client->waitForAttributeToContain('turbo-frame[id="' . $turboframeId . '"]', 'complete', 'true');
    }

    public function waitForDiv(string $divId): void
    {
        $this->client->waitForVisibility('div[id="' . $divId . '"]');
    }
}
