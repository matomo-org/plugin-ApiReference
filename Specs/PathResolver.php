<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Specs;

use Piwik\Container\Container;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;

class PathResolver
{
    private const SHARED_BASE_SUBDIRECTORY = '/OpenApiDocs/';

    private const SHARED_SPECS_SUBDIRECTORY = '/OpenApiDocs/specs/';

    private const SHARED_ANNOTATIONS_SUBDIRECTORY = '/OpenApiDocs/annotations/';

    private const SHARED_RESPONSES_SUBDIRECTORY = '/OpenApiDocs/responses/';

    private $pluginDirectory;

    private $isCloudActivated;

    private $container;

    public function __construct(?string $pluginDirectory = null, ?bool $isCloudActivated = null, ?Container $container = null)
    {
        $this->pluginDirectory = $pluginDirectory ?? Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $this->isCloudActivated = $isCloudActivated ?? Manager::getInstance()->isPluginActivated('Cloud');
        $this->container = $container ?? $this->getStaticContainer();
    }

    public function getSpecDirectory(): string
    {
        return $this->getArtifactDirectory(self::SHARED_SPECS_SUBDIRECTORY, OpenApiDocs::GENERATED_SPECS_PATH);
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
        return $this->getArtifactDirectory(self::SHARED_ANNOTATIONS_SUBDIRECTORY, OpenApiDocs::GENERATED_ANNOTATIONS_PATH);
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
        return $this->getArtifactDirectory(self::SHARED_RESPONSES_SUBDIRECTORY, OpenApiDocs::EXAMPLE_RESPONSES_PATH);
    }

    public function getExampleResponseFilePath(string $pluginName, string $methodName, string $format): string
    {
        return $this->getResponsesDirectory() . $pluginName . '.' . $methodName . '.' . strtolower($format);
    }

    private function getArtifactDirectory(string $sharedSubdirectory, string $fallbackPath): string
    {
        $sharedPath = $this->getSharedArtifactDirectory($sharedSubdirectory);
        if ($sharedPath !== null) {
            return $sharedPath;
        }

        return $this->pluginDirectory . $fallbackPath;
    }

    private function getSharedArtifactDirectory(string $sharedSubdirectory): ?string
    {
        if (!$this->isCloudActivated || $this->container === null || !$this->container->has('CloudDistributedCachePath')) {
            return null;
        }

        $sharedBasePath = trim((string) $this->container->get('CloudDistributedCachePath'));
        if ($sharedBasePath === '') {
            return null;
        }

        return rtrim($sharedBasePath, '/\\') . self::SHARED_BASE_SUBDIRECTORY . ltrim(substr($sharedSubdirectory, strlen(self::SHARED_BASE_SUBDIRECTORY)), '/\\');
    }

    private function getStaticContainer(): ?Container
    {
        try {
            return StaticContainer::getContainer();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
