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
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ApiReference\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group ApiReference
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var array<string, mixed>
     */
    private $backupGet;

    /**
     * @var array<string, mixed>
     */
    private $backupRequest;

    public function setUp(): void
    {
        parent::setUp();

        $this->backupGet = $_GET;
        $this->backupRequest = $_REQUEST;

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        Fixture::resetTranslations();
        Fixture::loadAllTranslations();

        Manager::getInstance()->loadPlugin('ApiReference');

        $_GET = [
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
        ];
        $_REQUEST = $_GET;

        $this->controller = new Controller();
    }

    public function tearDown(): void
    {
        $_GET = $this->backupGet;
        $_REQUEST = $this->backupRequest;

        Fixture::resetTranslations();

        parent::tearDown();
    }

    public function testSwaggerRendersAdminPageForViewAccess(): void
    {
        FakeAccess::clearAccess(
            $superUser = false,
            $idSitesAdmin = [0],
            $idSitesView = [1],
            $identity = 'viewAccessUser'
        );

        $html = $this->controller->swagger();

        $this->assertNotSame('', $html);
        $this->assertStringContainsString('vue-entry="ApiReference.SwaggerPage"', $html);
        $this->assertStringContainsString('default-website-id="1"', $html);
        $this->assertStringContainsString('piwik-url=', $html);
        $this->assertStringContainsString('plugins/ApiReference/vue/lib/swagger-ui/swagger-ui.css', $html);
        $this->assertStringContainsString('plugins/ApiReference/vue/src/SwaggerPage/swagger-ui-overrides.css', $html);
        $this->assertStringContainsString('plugins/ApiReference/vue/lib/swagger-ui/swagger-ui-bundle.js', $html);
        $this->assertStringContainsString('Swagger', $html);
    }

    public function testSwaggerThrowsWhenUserHasNoAccess(): void
    {
        $originalAccess = StaticContainer::getContainer()->get(Access::class);
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [], [], 'noAccess'));

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('checkUserHasSomeViewAccess');

            $this->controller->swagger();
        } finally {
            StaticContainer::getContainer()->set(Access::class, $originalAccess);
        }
    }
}
