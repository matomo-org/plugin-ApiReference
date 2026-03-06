<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit;

require_once PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\OpenApiDocs\API;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_APITest
 */
class APITest extends TestCase
{
    private $originalAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalAccess = Access::getInstance();
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [], [1], 'viewUser'));
    }

    protected function tearDown(): void
    {
        StaticContainer::getContainer()->set(Access::class, $this->originalAccess);

        parent::tearDown();
    }

    public function testGetMatomoOpenApiSpecReturnsDecodedJson()
    {
        $expectedSpec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Matomo Reporting API',
                'version' => '1.0.0',
            ],
        ];

        $api = $this->buildApiMock(true, json_encode($expectedSpec));

        $result = $api->getMatomoOpenApiSpec();

        $this->assertSame($expectedSpec, $result);
    }

    public function testGetMatomoOpenApiSpecThrowsExceptionWhenFileMissing()
    {
        $api = $this->buildApiMock(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file was not found');

        $api->getMatomoOpenApiSpec();
    }

    public function testGetMatomoOpenApiSpecThrowsExceptionWhenJsonIsInvalid()
    {
        $api = $this->buildApiMock(true, '{invalid json}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file contains invalid JSON');

        $api->getMatomoOpenApiSpec();
    }

    private function buildApiMock(bool $isReadable, $fileContents = false): API
    {
        $api = $this->getMockBuilder(API::class)
            ->onlyMethods(['getMatomoSpecFilePath', 'isSpecFileReadable', 'readSpecFile'])
            ->getMock();

        $api->method('getMatomoSpecFilePath')->willReturn('/tmp/matomo_openapi_spec_v1.0.0.json');
        $api->method('isSpecFileReadable')->willReturn($isReadable);
        $api->method('readSpecFile')->willReturn($fileContents);

        return $api;
    }
}
