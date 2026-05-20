<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\Generation;

use Piwik\Plugins\ApiReference\Annotations\AnnotationGenerator;
use Piwik\Plugins\ApiReference\ApiReference;
use Piwik\Plugins\ApiReference\Specs\SpecGenerator;

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

    /**
     * Generate an OpenAPI spec for one or more comma-separated plugin names.
     *
     * @param string $pluginNames Comma-separated plugin names to include in the generated spec.
     * @param string $format Output format for the spec, for example `json` or `yaml`.
     * @param string $version Version string written into the generated OpenAPI spec.
     * @param bool $writeToFile Whether the generated spec should also be written to the plugin tmp specs directory.
     * @param bool $addAnnotations Whether API annotations should be regenerated before building the spec.
     * @return string The generated OpenAPI spec contents.
     * @throws \RuntimeException If no non-empty plugin names are provided.
     */
    public function generateSpecForPlugins(
        string $pluginNames,
        string $format = 'json',
        string $version = ApiReference::DEFAULT_SPEC_VERSION,
        bool $writeToFile = false,
        bool $addAnnotations = false
    ): string {
        $parsedPluginNames = $this->getPluginNames($pluginNames);

        if ($addAnnotations) {
            $this->generateAnnotations($parsedPluginNames);
        }

        return $this->specGenerator->generateSpec($parsedPluginNames, $format, $version, $writeToFile);
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
