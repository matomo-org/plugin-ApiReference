<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Integration;

use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Controller;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group OpenApiDocs
 * @group ControllerTest
 * @group Plugins
 */
class ControllerTest extends IntegrationTestCase
{
    private Controller $controller;

    /**
     * @var array<string, mixed>
     */
    private array $backupGet;

    /**
     * @var array<string, mixed>
     */
    private array $backupRequest;

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

        Manager::getInstance()->loadPlugin('OpenApiDocs');

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

    public function testSwaggerRendersAdminPageForSuperUser(): void
    {
        FakeAccess::clearAccess(
            $superUser = true,
            $idSitesAdmin = [1],
            $idSitesView = [1],
            $identity = 'superUserLogin'
        );

        $html = $this->controller->swagger();

        $this->assertNotSame('', $html);
        $this->assertStringContainsString('vue-entry="OpenApiDocs.SwaggerPage"', $html);
        $this->assertStringContainsString('Swagger', $html);
    }

    public function testSwaggerThrowsWhenUserIsNotSuperUser(): void
    {
        $originalAccess = StaticContainer::getContainer()->get(Access::class);
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [], [1], 'viewUser'));

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('checkUserHasSuperUserAccess');

            $this->controller->swagger();
        } finally {
            StaticContainer::getContainer()->set(Access::class, $originalAccess);
        }
    }
}
