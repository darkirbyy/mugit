<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Tests\Extension\CoreAwareTrait;
use PHPUnit\Framework\Attributes as PU;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

#[PU\CoversNothing]
abstract class E2EControllerTest extends PantherTestCase
{
    use CoreAwareTrait;

    protected Client $client;
    protected static int $TIMEOUT = 5;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createPantherClient();
        self::coreInitAndReset();
    }

    public function clickElement(string $elementId): void
    {
        $this->assertSelectorExists('[id="' . $elementId . '"]');
        $this->client->executeScript("document.querySelector('[id=" . $elementId . "]').click()");
    }

    public function submitForm(string $formId, array $values = []): void
    {
        foreach (array_keys($values) as $key) {
            $this->assertSelectorExists('input[id="' . $formId . '-' . $key . '"]');
        }
        $this->assertSelectorExists('button[id="' . $formId . '-submit"]');
        $this->client->submitForm($formId . '-submit', $values, 'POST', ['HTTP_Turbo_Frame' => 'true']);
    }

    public function waitForTurboframeLoaded(string ...$turboframeIdList): void
    {
        foreach ($turboframeIdList as $turboframeId) {
            $this->client->waitForAttributeToContain('turbo-frame[id="' . $turboframeId . '"]', 'complete', 'true', self::$TIMEOUT);
        }
    }

    public function waitForTurboframeReplace(string ...$turboframeIdList): void
    {
        foreach ($turboframeIdList as $turboframeId) {
            $this->client->waitForStaleness('turbo-frame[id="' . $turboframeId . '"]', self::$TIMEOUT);
        }
    }

    public function waitForDiv(string ...$divIdList): void
    {
        foreach ($divIdList as $divId) {
            $this->client->waitForVisibility('div[id="' . $divId . '"]', self::$TIMEOUT);
        }
    }
}
