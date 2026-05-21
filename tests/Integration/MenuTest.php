<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Integration;

use Piwik\Cache;
use Piwik\Menu\MenuAdmin;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ApiReference
 * @group MenuTest
 * @group Plugins
 */
class MenuTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Manager::getInstance()->loadPlugin('ApiReference');
    }

    public function provideContainerConfig(): array
    {
        return ['Piwik\Access' => new FakeAccess()];
    }

    public function tearDown(): void
    {
        Cache::getTransientCache()->flushAll();
        MenuAdmin::unsetInstance();

        parent::tearDown();
    }

    public function testConfigureAdminMenuAddsApiItemForViewAccess(): void
    {
        FakeAccess::clearAccess(
            $superUser = false,
            $idSitesAdmin = [0],
            $idSitesView = [1],
            $identity = 'viewAccessUser'
        );

        $items = $this->buildConfiguredMenu()->getMenu();

        $this->assertArrayHasKey('CorePluginsAdmin_MenuPlatform', $items);
        $this->assertArrayHasKey('General_API', $items['CorePluginsAdmin_MenuPlatform']);
    }

    public function testConfigureAdminMenuSkipsApiItemWithoutViewAccess(): void
    {
        FakeAccess::clearAccess(
            $superUser = false,
            $idSitesAdmin = [],
            $idSitesView = [],
            $identity = 'noAccessUser'
        );

        $items = $this->buildConfiguredMenu()->getMenu();

        if (!isset($items['CorePluginsAdmin_MenuPlatform'])) {
            $this->assertArrayNotHasKey('CorePluginsAdmin_MenuPlatform', $items);
            return;
        }

        $this->assertArrayNotHasKey('General_API', $items['CorePluginsAdmin_MenuPlatform']);
    }

    private function buildConfiguredMenu(): MenuAdmin
    {
        MenuAdmin::unsetInstance();
        return MenuAdmin::getInstance();
    }
}
