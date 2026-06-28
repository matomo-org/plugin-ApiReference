<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Integration;

use Piwik\Access;
use Piwik\Cache;
use Piwik\Container\StaticContainer;
use Piwik\Menu\MenuAdmin;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Version;

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

        // On older Matomo cores the bundled (latest) TagManager plugin calls
        // Piwik\Url::getExternalLinkTag() while creating the default container
        // during site creation. That method was only added in 5.6.0-b1, so
        // creating a website crashes on earlier cores. Skip there.
        if (version_compare(Version::VERSION, '5.6.0-b1', '<')) {
            self::markTestSkipped('Bundled TagManager requires Url::getExternalLinkTag(), added in Matomo 5.6.0-b1');
        }

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

    public function testConfigureAdminMenuEditsApiItemUrlForViewAccess(): void
    {
        $originalAccess = StaticContainer::getContainer()->get(Access::class);
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [0], [1], 'viewAccessUser'));

        try {
            $items = $this->buildConfiguredMenu()->getMenu();

            $this->assertArrayHasKey('CorePluginsAdmin_MenuPlatform', $items);
            $this->assertArrayHasKey('General_API', $items['CorePluginsAdmin_MenuPlatform']);
            $this->assertSame(
                ['action' => 'swagger', 'module' => 'ApiReference'],
                $items['CorePluginsAdmin_MenuPlatform']['General_API']['_url']
            );
        } finally {
            StaticContainer::getContainer()->set(Access::class, $originalAccess);
        }
    }

    private function buildConfiguredMenu(): MenuAdmin
    {
        MenuAdmin::unsetInstance();
        return MenuAdmin::getInstance();
    }
}
