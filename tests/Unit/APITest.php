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

    public function testGetOpenApiSpecKeepsSuccessfulExamplesForSuperUsers(): void
    {
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(true, [], [1], 'superUser'));

        $expectedSpec = $this->getSpecFixtureWithResponseExamples();
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, json_encode($expectedSpec));

        $result = $api->getOpenApiSpec('CustomAlerts');

        $this->assertSame($expectedSpec, $result);
    }

    public function testGetOpenApiSpecRemovesOnlySuccessfulExamplesForUsersWithoutSiteOneAccess(): void
    {
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [], [2], 'otherViewer'));

        $api = $this->buildApiMock(
            '/tmp/CustomAlerts_openapi_spec_v1.0.0.json',
            true,
            json_encode($this->getSpecFixtureWithResponseExamples())
        );
        $expectedTryItOutNote = $this->callProtectedMethod($api, 'getTryItOutNote');

        $result = $api->getOpenApiSpec('CustomAlerts');

        $this->assertArrayNotHasKey('example', $result['paths']['/endpoint']['get']['responses']['200']['content']['application/json']);
        $this->assertArrayNotHasKey('examples', $result['paths']['/endpoint']['get']['responses']['200']['content']['application/json']);
        $this->assertArrayNotHasKey('schema', $result['paths']['/endpoint']['get']['responses']['200']['content']['application/json']);
        $this->assertSame(
            'kept error example',
            $result['paths']['/endpoint']['get']['responses']['400']['content']['application/json']['example']
        );
        $this->assertSame(
            ['type' => 'string', 'example' => 'stay put'],
            $result['paths']['/endpoint']['get']['parameters'][0]['schema']
        );
        $this->assertSame(
            ['value' => ['id' => 99]],
            $result['paths']['/endpoint']['get']['requestBody']['content']['application/json']['examples']['request']
        );
        $this->assertSame(
            'Success' . $expectedTryItOutNote,
            $result['paths']['/endpoint']['get']['responses']['200']['description']
        );
    }

    public function testGetOpenApiSpecDoesNotDuplicateTryItOutNote(): void
    {
        StaticContainer::getContainer()->set(Access::class, new FakeAccess(false, [], [2], 'otherViewer'));

        $spec = $this->getSpecFixtureWithResponseExamples();
        $api = $this->buildApiMock('/tmp/CustomAlerts_openapi_spec_v1.0.0.json', true, json_encode($spec));
        $expectedTryItOutNote = $this->callProtectedMethod($api, 'getTryItOutNote');
        $spec['paths']['/endpoint']['get']['responses']['200']['description'] .= $expectedTryItOutNote;
        $api->method('readSpecFile')->willReturn(json_encode($spec));

        $result = $api->getOpenApiSpec('CustomAlerts');

        $this->assertSame(
            'Success' . $expectedTryItOutNote,
            $result['paths']['/endpoint']['get']['responses']['200']['description']
        );
    }

    public function testRemoveSuccessfulResponseExamplesLeavesOperationWithoutResponsesUnchanged(): void
    {
        $api = new API();
        $spec = [
            'paths' => [
                '/endpoint' => [
                    'get' => [
                        'summary' => 'No responses here',
                    ],
                ],
            ],
        ];

        $this->assertSame($spec, $this->callProtectedMethod($api, 'removeSuccessfulResponseExamples', [$spec]));
    }

    public function testRemoveSuccessfulResponseExamplesLeavesOperationWithoutSuccessfulResponseUnchanged(): void
    {
        $api = new API();
        $spec = [
            'paths' => [
                '/endpoint' => [
                    'get' => [
                        'responses' => [
                            '400' => [
                                'description' => 'Error',
                                'content' => [
                                    'application/json' => [
                                        'example' => 'keep me',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame($spec, $this->callProtectedMethod($api, 'removeSuccessfulResponseExamples', [$spec]));
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
        $this->expectExceptionMessage('General_ExceptionInvalidReportRendererFormat');

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
     * @return array<string, mixed>
     */
    private function getSpecFixtureWithResponseExamples(): array
    {
        return [
            'openapi' => '3.1.0',
            'paths' => [
                '/endpoint' => [
                    'get' => [
                        'parameters' => [
                            [
                                'name' => 'label',
                                'schema' => [
                                    'type' => 'string',
                                    'example' => 'stay put',
                                ],
                            ],
                        ],
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'examples' => [
                                        'request' => [
                                            'value' => ['id' => 99],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Success',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'row' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'additionalProperties' => true,
                                                    ],
                                                ],
                                            ],
                                        ],
                                        'example' => ['value' => 'remove me'],
                                        'examples' => [
                                            'success' => [
                                                'value' => ['another' => 'remove me'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '400' => [
                                'description' => 'Error',
                                'content' => [
                                    'application/json' => [
                                        'example' => 'kept error example',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
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
