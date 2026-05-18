<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Unit\Specs;

require_once PIWIK_INCLUDE_PATH . '/plugins/ApiReference/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ApiReference\Specs\PathResolver;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_PathResolverTest
 */
class PathResolverTest extends TestCase
{
    public function testReturnsPluginLocalPathsWhenCloudIsDisabled(): void
    {
        $resolver = new PathResolver('/plugins/ApiReference');

        $this->assertSame('/plugins/ApiReference/tmp/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/plugins/ApiReference/tmp/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/plugins/ApiReference/tmp/responses/', $resolver->getResponsesDirectory());
    }

    public function testReturnsOverriddenPathsWhenArtifactBasePathEventProvidesOne(): void
    {
        $resolver = $this->buildPathResolverWithArtifactBasePath('/cache/distributed/ApiReference');

        $this->assertSame('/cache/distributed/ApiReference/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/cache/distributed/ApiReference/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/cache/distributed/ApiReference/responses/', $resolver->getResponsesDirectory());
    }

    public function testTrimsTrailingSlashesFromOverriddenArtifactBasePath(): void
    {
        $resolver = $this->buildPathResolverWithArtifactBasePath('/cache/distributed/ApiReference/');

        $this->assertSame('/cache/distributed/ApiReference/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/cache/distributed/ApiReference/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/cache/distributed/ApiReference/responses/', $resolver->getResponsesDirectory());
    }

    public function testKeepsDefaultLocalPathsWhenEventDoesNotProvideOverride(): void
    {
        $resolver = $this->buildPathResolverWithArtifactBasePath(null);

        $this->assertSame('/plugins/ApiReference/tmp/specs/', $resolver->getSpecDirectory());
        $this->assertSame('/plugins/ApiReference/tmp/annotations/', $resolver->getAnnotationsDirectory());
        $this->assertSame('/plugins/ApiReference/tmp/responses/', $resolver->getResponsesDirectory());
    }

    public function testBuildsFilePathsUsingExpectedNamingConventions(): void
    {
        $resolver = $this->buildPathResolverWithArtifactBasePath('/cache/distributed/ApiReference/');

        $this->assertSame(
            '/cache/distributed/ApiReference/specs/CustomAlerts_openapi_spec_v2.0.0.yaml',
            $resolver->getSpecFilePath('CustomAlerts', '2.0.0', 'YAML')
        );
        $this->assertSame(
            '/cache/distributed/ApiReference/annotations/CustomAlertsGeneratedAnnotations.php',
            $resolver->getAnnotationFilePath('CustomAlerts')
        );
        $this->assertSame(
            '/cache/distributed/ApiReference/annotations/matomo_api_method_info.json',
            $resolver->getApiMethodInfoFilePath('matomo')
        );
        $this->assertSame(
            '/cache/distributed/ApiReference/responses/CustomAlerts.getAlerts.json',
            $resolver->getExampleResponseFilePath('CustomAlerts', 'getAlerts', 'JSON')
        );
    }

    private function buildPathResolverWithArtifactBasePath(?string $artifactBasePath): PathResolver
    {
        $resolver = $this->getMockBuilder(PathResolver::class)
            ->setConstructorArgs(['/plugins/ApiReference'])
            ->onlyMethods(['dispatchArtifactBasePathEvent'])
            ->getMock();

        $resolver->method('dispatchArtifactBasePathEvent')
            ->willReturnCallback(static function (?string &$resolvedArtifactBasePath) use ($artifactBasePath): void {
                $resolvedArtifactBasePath = $artifactBasePath;
            });

        return $resolver;
    }
}
