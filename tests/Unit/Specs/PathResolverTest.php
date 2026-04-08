<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit\Specs;

require_once PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Container\Container;
use Piwik\Plugins\OpenApiDocs\Specs\PathResolver;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_PathResolverTest
 */
class PathResolverTest extends TestCase
{
    public function testReturnsPluginLocalPathsWhenCloudIsDisabled(): void
    {
        $resolver = new PathResolver('/plugins/OpenApiDocs', false);

        $this->assertSame('/plugins/OpenApiDocs/tmp/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/responses/', $resolver->getResponsesDirectory());
    }

    public function testReturnsSharedPathsWhenCloudIsEnabledAndDistributedCachePathExists(): void
    {
        $resolver = $this->buildPathResolverWithSharedPathValidationResult(true, '/cache/distributed', true);

        $this->assertSame('/cache/distributed/OpenApiDocs/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/cache/distributed/OpenApiDocs/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/cache/distributed/OpenApiDocs/responses/', $resolver->getResponsesDirectory());
    }

    public function testFallsBackToPluginLocalPathsWhenCloudCachePathIsMissing(): void
    {
        $resolver = new PathResolver('/plugins/OpenApiDocs', true, $this->buildContainerStub(false));

        $this->assertSame('/plugins/OpenApiDocs/tmp/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/responses/', $resolver->getResponsesDirectory());
    }

    public function testFallsBackToPluginLocalPathsWhenCloudCachePathIsEmpty(): void
    {
        $resolver = new PathResolver('/plugins/OpenApiDocs', true, $this->buildContainerStub(true, '   '));

        $this->assertSame('/plugins/OpenApiDocs/tmp/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/plugins/OpenApiDocs/tmp/responses/', $resolver->getResponsesDirectory());
    }

    public function testBuildsFilePathsUsingExpectedNamingConventions(): void
    {
        $resolver = $this->buildPathResolverWithSharedPathValidationResult(true, '/cache/distributed/', true);

        $this->assertSame(
            '/cache/distributed/OpenApiDocs/specs/CustomAlerts_openapi_spec_v2.0.0.yaml',
            $resolver->getSpecFilePath('CustomAlerts', '2.0.0', 'YAML')
        );
        $this->assertSame(
            '/cache/distributed/OpenApiDocs/annotations/CustomAlertsGeneratedAnnotations.php',
            $resolver->getAnnotationFilePath('CustomAlerts')
        );
        $this->assertSame(
            '/cache/distributed/OpenApiDocs/annotations/matomo_api_method_info.json',
            $resolver->getApiMethodInfoFilePath('matomo')
        );
        $this->assertSame(
            '/cache/distributed/OpenApiDocs/responses/CustomAlerts.getAlerts.json',
            $resolver->getExampleResponseFilePath('CustomAlerts', 'getAlerts', 'JSON')
        );
    }

    private function buildContainerStub(bool $hasDistributedCachePath, string $distributedCachePath = ''): Container
    {
        $container = $this->getMockBuilder(Container::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['has', 'get'])
            ->getMock();

        $container->method('has')
            ->with('CloudDistributedCachePath')
            ->willReturn($hasDistributedCachePath);

        if ($hasDistributedCachePath) {
            $container->method('get')
                ->with('CloudDistributedCachePath')
                ->willReturn($distributedCachePath);
        }

        return $container;
    }

    private function buildPathResolverWithSharedPathValidationResult(
        bool $hasDistributedCachePath,
        string $distributedCachePath,
        bool $isUsableSharedBasePath
    ): PathResolver {
        $resolver = $this->getMockBuilder(PathResolver::class)
            ->setConstructorArgs(['/plugins/OpenApiDocs', true, $this->buildContainerStub($hasDistributedCachePath, $distributedCachePath)])
            ->onlyMethods(['isUsableSharedBasePath'])
            ->getMock();

        $resolver->method('isUsableSharedBasePath')
            ->with(trim($distributedCachePath))
            ->willReturn($isUsableSharedBasePath);

        return $resolver;
    }
}
