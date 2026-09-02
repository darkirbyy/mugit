<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\Attributes as PU;

final class HomeScenarioTest extends E2EControllerTest
{
    #[PU\Test]
    public function homeCheckMenuAdmin(): void
    {
        // Start main request
        $this->client->request('GET', '/switch?is-admin=true');
        $this->client->request('GET', '/');
        $this->waitForTurboframeLoaded('turboframe-repo-list');

        // Check the user button content
        $this->assertAnySelectorTextContains('span', 'user1');

        // Click the menu button
        $this->clickElement('button-navbar-menu');
        $this->waitForDiv('dropdown-navbar-menu');

        // Check navbar admin menu content
        $this->assertAnySelectorTextContains('div', '(layout.navbar.admin)');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.administrate');

        // Click the admin button
        $this->clickElement('button-navbar-admin');

        // Check tool sub-menu content
        $this->assertAnySelectorTextContains('span', 'layout.navbar.tool.users');
        $this->assertAnySelectorTextContains('span', 'layout.navbar.tool.logs');
    }

    #[PU\Test]
    public function homeChangeThemeToDark(): void
    {
        // Start main request
        $this->client->request('GET', '/switch?is-admin=false');
        $this->client->request('GET', '/');
        $this->waitForTurboframeLoaded('turboframe-repo-list');

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
        $this->assertAnySelectorTextNotContains('div', '(layout.navbar.admin)');
        $this->assertAnySelectorTextNotContains('span', 'layout.navbar.administrate');

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
