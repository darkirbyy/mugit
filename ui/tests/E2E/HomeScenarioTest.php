<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class HomeScenarioTest extends E2EControllerTest
{
    #[PU\Test]
    public function homeChangeThemeToDark(): void
    {
        // Start main request
        $this->client->request('GET', '/');
        $this->waitForTurboframe('turboframe-repo-list');

        // Check the user button content
        $this->assertAnySelectorTextContains('span', 'user1');

        // Click the menu button
        $this->clickElement('button-navbar-menu');
        $this->waitForDiv('dropdown-navbar-menu');

        // Check navbar menu content
        $this->assertAnySelectorTextContains('span', 'layout.navbar.mySSHKeys');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.appearance');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.myAccount');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.backToHub');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.signOut');

        // Click the appearance button
        $this->clickElement('button-navbar-appearance');

        // Check appearance sub-menu content
        $this->assertAnySelectorTextContains('span', 'layout.navbar.theme.light');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.theme.dark');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.theme.auto');

        // Click the dark theme button
        $this->clickElement('button-navbar-theme-dark');

        // Check dark attribute on html tag
        $this->assertSelectorAttributeContains('html', 'class', 'dark');
    }
}
