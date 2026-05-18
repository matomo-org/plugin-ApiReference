<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Unit;

require_once PIWIK_INCLUDE_PATH . '/plugins/ApiReference/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Access;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\ApiReference\API;
use Piwik\Plugins\ApiReference\Generation\PluginListProvider;
use Piwik\Plugins\ApiReference\Specs\PathResolver;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_APITest
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

    public function testGetAllowedPluginsReturnsProviderValues(): void
    {
        $provider = $this->createMock(PluginListProvider::class);
        $provider->expects($this->once())
            ->method('getAllowedPlugins')
            ->willReturn(['Login', 'ActivityLog']);

        $api = $this->getMockBuilder(API::class)
            ->onlyMethods(['getPluginListProvider'])
            ->getMock();
        $api->method('getPluginListProvider')->willReturn($provider);

        $this->assertSame(['Login', 'ActivityLog'], $api->getAllowedPlugins());
    }

    public function testGetAllowedPluginMetadataReturnsProviderValues(): void
    {
        $provider = $this->createMock(PluginListProvider::class);
        $provider->expects($this->once())
            ->method('getAllowedPluginMetadata')
            ->willReturn([
                'Login' => ['description' => 'Login API description'],
                'ActivityLog' => ['description' => ''],
            ]);

        $api = $this->getMockBuilder(API::class)
            ->onlyMethods(['getPluginListProvider'])
            ->getMock();
        $api->method('getPluginListProvider')->willReturn($provider);

        $this->assertSame([
            'Login' => ['description' => 'Login API description'],
            'ActivityLog' => ['description' => ''],
        ], $api->getAllowedPluginMetadata());
    }

    public function testGetOpenApiSpecThrowsExceptionWhenFileMissing()
    {
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file was not found');

        $api->getOpenApiSpec('CustomAlerts');
    }

    public function testGetOpenApiSpecThrowsExceptionWhenJsonIsInvalid()
    {
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, '{invalid json}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('OpenAPI spec file contains invalid JSON');

        $api->getOpenApiSpec('CustomAlerts');
    }

    public function testGetOpenApiSpecThrowsExceptionWhenFormatIsInvalid()
    {
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, '{}');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Report format 'yaml' not valid");

        $api->getOpenApiSpec('CustomAlerts', 'yaml');
    }

    public function testGetOpenApiSpecThrowsExceptionWhenSpecIsNotAValidPlugin()
    {
        $api = new API();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid plugin name: DefinitelyNotARealPlugin');

        $api->getOpenApiSpec('DefinitelyNotARealPlugin');
    }

    public function testGetSpecFilePathDelegatesToPathResolver()
    {
        $pathResolver = $this->createMock(PathResolver::class);
        $pathResolver->expects($this->once())
            ->method('getSpecFilePath')
            ->with('CustomAlerts')
            ->willReturn('/shared/specs/CustomAlerts_openapi_spec_v1.0.0.json');

        $api = $this->getMockBuilder(API::class)
            ->onlyMethods(['getSpecPathResolver'])
            ->getMock();
        $api->method('getSpecPathResolver')->willReturn($pathResolver);

        $this->assertSame(
            '/shared/specs/CustomAlerts_openapi_spec_v1.0.0.json',
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
