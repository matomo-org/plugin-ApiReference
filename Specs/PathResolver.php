<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Specs;

use Piwik\Plugin\Manager;
use Piwik\Piwik;

class PathResolver
{
    private const ARTIFACT_BASE_SUBDIRECTORY = '/tmp/';

    private const SPECS_SUBDIRECTORY = 'specs/';

    private const ANNOTATIONS_SUBDIRECTORY = 'annotations/';

    private const RESPONSES_SUBDIRECTORY = 'responses/';

    private $pluginDirectory;

    public function __construct(?string $pluginDirectory = null)
    {
        $this->pluginDirectory = $pluginDirectory ?? Manager::getInstance()::getPluginDirectory('OpenApiDocs');
    }

    public function getSpecDirectory(): string
    {
        return $this->getArtifactDirectory(self::SPECS_SUBDIRECTORY);
    }

    public function getSpecFilePath(
        string $specFileBaseName,
        string $version = OpenApiDocs::DEFAULT_SPEC_VERSION,
        string $format = 'json'
    ): string {
        return $this->getSpecDirectory() . $specFileBaseName . '_openapi_spec_v' . $version . '.' . strtolower($format);
    }

    public function getAnnotationsDirectory(): string
    {
        return $this->getArtifactDirectory(self::ANNOTATIONS_SUBDIRECTORY);
    }

    public function getAnnotationFilePath(string $pluginName): string
    {
        return $this->getAnnotationsDirectory() . $pluginName . 'GeneratedAnnotations.php';
    }

    public function getApiMethodInfoFilePath(string $fileBaseName): string
    {
        return $this->getAnnotationsDirectory() . $fileBaseName . '_api_method_info.json';
    }

    public function getResponsesDirectory(): string
    {
        return $this->getArtifactDirectory(self::RESPONSES_SUBDIRECTORY);
    }

    public function getExampleResponseFilePath(string $pluginName, string $methodName, string $format): string
    {
        return $this->getResponsesDirectory() . $pluginName . '.' . $methodName . '.' . strtolower($format);
    }

    private function getArtifactDirectory(string $subdirectory): string
    {
        return $this->getArtifactBasePath() . $subdirectory;
    }

    private function getArtifactBasePath(): string
    {
        $defaultArtifactBasePath = $this->pluginDirectory . self::ARTIFACT_BASE_SUBDIRECTORY;
        $artifactBasePath = $defaultArtifactBasePath;
        $this->dispatchArtifactBasePathEvent($artifactBasePath);

        if (empty($artifactBasePath)) {
            $artifactBasePath = $defaultArtifactBasePath;
        }

        return rtrim($artifactBasePath, '/\\') . '/';
    }

    protected function dispatchArtifactBasePathEvent(?string &$artifactBasePath): void
    {
        Piwik::postEvent('OpenApiDocs.getArtifactBasePath', [&$artifactBasePath]);
    }
}
