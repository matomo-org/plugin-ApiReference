<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Generation;

use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;
use Piwik\Plugins\OpenApiDocs\Specs\SpecGenerator;

class SpecGenerationService
{
    /**
     * @var AnnotationGenerator
     */
    private $annotationGenerator;

    /**
     * @var SpecGenerator
     */
    private $specGenerator;

    public function __construct(AnnotationGenerator $annotationGenerator, SpecGenerator $specGenerator)
    {
        $this->annotationGenerator = $annotationGenerator;
        $this->specGenerator = $specGenerator;
    }

    public function generateSpecForPlugins(
        string $pluginNames,
        string $format = 'json',
        string $version = OpenApiDocs::DEFAULT_SPEC_VERSION,
        bool $writeToFile = false,
        bool $addAnnotations = false
    ): string {
        $parsedPluginNames = $this->getPluginNames($pluginNames);

        if ($addAnnotations) {
            $this->generateAnnotations($parsedPluginNames);
        }

        return $this->specGenerator->generatePluginDoc($pluginNames, $format, $version, $writeToFile);
    }

    /**
     * @param string[] $pluginNames
     */
    private function generateAnnotations(array $pluginNames): void
    {
        foreach ($pluginNames as $pluginName) {
            $this->annotationGenerator->generatePluginApiAnnotations($pluginName, true);
        }
    }

    /**
     * @return string[]
     */
    private function getPluginNames(string $pluginNames): array
    {
        $plugins = array_filter(array_map('trim', explode(',', $pluginNames)), static function (string $pluginName): bool {
            return $pluginName !== '';
        });

        if (empty($plugins)) {
            throw new \RuntimeException('At least one plugin name is required.');
        }

        return array_values($plugins);
    }
}
