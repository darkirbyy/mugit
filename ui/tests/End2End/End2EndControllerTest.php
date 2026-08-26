<?php

declare(strict_types=1);

namespace App\Tests\End2End;

use App\Service\CoreExecInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

abstract class End2EndControllerTest extends PantherTestCase
{
    protected CoreExecInterface $coreExec;
    protected Client $client;

    #[\Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->client = static::createPantherClient();
        $this->coreExec = self::getContainer()->get(CoreExecInterface::class);
    }

    public function clickButton(string $buttonId): void
    {
        $this->client->executeScript("document.querySelector('button[id=" . $buttonId . "]').click()");
    }

    public function submitForm(string $buttonId, array $values): void
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
