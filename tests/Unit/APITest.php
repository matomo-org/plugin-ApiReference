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

    public function testGetOpenApiSpecReturnsDecodedJsonForMatomo()
    {
        $expectedSpec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Matomo Reporting API',
                'version' => '1.0.0',
            ],
        ];

        $api = $this->buildApiMock('/tmp/matomo_openapi_spec_v1.0.0.json', true, json_encode($expectedSpec));

        $result = $api->getOpenApiSpec();

        $this->assertSame($expectedSpec, $result);
    }

    public function testGetOpenApiSpecReturnsDecodedJsonForPlugin()
    {
        $expectedSpec = [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'Matomo Reporting API for CustomAlerts plugin',
                'version' => '1.0.0',
            ],
        ];

        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, json_encode($expectedSpec));

        $result = $api->getOpenApiSpec('CustomAlerts');

        $this->assertSame($expectedSpec, $result);
    }

    public function testGetOpenApiSpecThrowsExceptionWhenFileMissing()
    {
        $api = $this->buildApiMock('/tmp/matomo_openapi_spec_v1.0.0.json', false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file was not found');

        $api->getOpenApiSpec();
    }

    public function testGetOpenApiSpecThrowsExceptionWhenJsonIsInvalid()
    {
        $api = $this->buildApiMock('/tmp/matomo_openapi_spec_v1.0.0.json', true, '{invalid json}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file contains invalid JSON');

        $api->getOpenApiSpec();
    }

    public function testGetOpenApiSpecThrowsExceptionWhenFormatIsInvalid()
    {
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, '{}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('General_ExceptionInvalidReportRendererFormat');

        $api->getOpenApiSpec('CustomAlerts', 'yaml');
    }

    public function testGetSpecFilePathUsesMatomoFileNameByDefault()
    {
        $api = new API();

        $this->assertSame(
            PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/tmp/specs/matomo_openapi_spec_v1.0.0.json',
            $this->callProtectedMethod($api, 'getSpecFilePath', ['matomo'])
        );
    }

    public function testGetSpecFilePathUsesPluginSpecificFileName()
    {
        $api = new API();

        $this->assertSame(
            PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/tmp/specs/CustomAlerts_openapi_spec_v1.0.0.json',
            $this->callProtectedMethod($api, 'getSpecFilePath', ['CustomAlerts'])
        );
    }


    private function buildApiMock(string $filePath, bool $isReadable, $fileContents = false): API
    {
        $api = $this->getMockBuilder(API::class)
            ->onlyMethods(['getSpecFilePath', 'isSpecFileReadable', 'readSpecFile'])
            ->getMock();

        $api->method('getSpecFilePath')->willReturn($filePath);
        $api->method('isSpecFileReadable')->willReturn($isReadable);
        $api->method('readSpecFile')->willReturn($fileContents);

        return $api;
    }

    /**
     * @param object $object
     * @param string $methodName
     * @param array<int, mixed> $arguments
     * @return mixed
     */
    private function callProtectedMethod($object, string $methodName, array $arguments = [])
    {
        $reflection = new \ReflectionMethod($object, $methodName);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $arguments);
    }
}
