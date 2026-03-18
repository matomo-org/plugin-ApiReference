<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit\Generation;

require_once PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;
use Piwik\Plugins\OpenApiDocs\Generation\SpecGenerationService;
use Piwik\Plugins\OpenApiDocs\Specs\SpecGenerator;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_SpecGenerationServiceTest
 */
class SpecGenerationServiceTest extends TestCase
{
    public function testGenerateSpecForPluginsThrowsForEmptyPluginNames(): void
    {
        $service = new SpecGenerationService(
            $this->createStub(AnnotationGenerator::class),
            $this->createStub(SpecGenerator::class)
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('At least one plugin name is required.');

        $service->generateSpecForPlugins(' , ', 'json', '1.0.0', false, false);
    }

    public function testGenerateSpecForPluginsWorksForValidPluginName(): void
    {
        $annotationGenerator = $this->createMock(AnnotationGenerator::class);
        $specGenerator = $this->getMockBuilder(SpecGenerator::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateSpec'])
            ->getMock();

        $annotationGenerator->expects($this->once())
            ->method('generatePluginApiAnnotations')
            ->with('CustomAlerts', true);
        $specGenerator->expects($this->once())
            ->method('generateSpec')
            ->with(['CustomAlerts'], 'json', '1.0.0', true)
            ->willReturn('spec body');

        $service = new SpecGenerationService($annotationGenerator, $specGenerator);

        $service->generateSpecForPlugins('CustomAlerts', 'json', '1.0.0', true, true);
    }
}
