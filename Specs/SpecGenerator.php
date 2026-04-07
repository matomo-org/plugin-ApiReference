<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs\Specs;

use Matomo\Dependencies\OpenApiDocs\OpenApi\Annotations\OpenApi;
use Matomo\Dependencies\OpenApiDocs\OpenApi\Generator;
use Piwik\Container\StaticContainer;
use Piwik\Exception\PluginNotFoundException;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Artifact\ArtifactWriter;
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;
use Piwik\SettingsPiwik;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

class SpecGenerator
{
    /**
     * @var PathResolver
     */
    private $specPathResolver;

    /**
     * @var ArtifactWriter
     */
    private $artifactWriter;

    public function __construct(?PathResolver $specPathResolver = null, ?ArtifactWriter $artifactWriter = null)
    {
        $this->specPathResolver = $specPathResolver ?? new PathResolver();
        $this->artifactWriter = $artifactWriter ?? new ArtifactWriter();

        // Set the constant for the current instance's URL
        if (!defined('LOCAL_MATOMO_SERVER_URL')) {
            define('LOCAL_MATOMO_SERVER_URL', SettingsPiwik::getPiwikUrl());
        }
    }

    /**
     * Generate an OpenAPI spec for a single plugin.
     *
     * @param string $pluginName
     * @param string $format
     * @param string $version
     * @param bool $writeToFile
     *
     * @return string
     * @throws \Exception
     */
    public function generatePluginDoc(string $pluginName, string $format = 'json', string $version = OpenApiDocs::DEFAULT_SPEC_VERSION, bool $writeToFile = false): string
    {
        return $this->generateSpec(explode(',', $pluginName), $format, $version, $writeToFile);
    }

    /**
     * Generate an OpenAPI spec for one or more plugins.
     *
     * @param array $pluginNames
     * @param string $format
     * @param string $version
     * @param bool $writeToFile
     *
     * @return string
     * @throws \Piwik\Exception\DI\DependencyException
     * @throws \Piwik\Exception\DI\NotFoundException
     * @throws PluginNotFoundException
     * @throws \Exception
     */
    public function generateSpec(array $pluginNames, string $format = 'json', string $version = OpenApiDocs::DEFAULT_SPEC_VERSION, bool $writeToFile = false): string
    {
        BaseValidator::check('pluginNames', $pluginNames, [new NotEmpty()]);
        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');

        foreach ($pluginNames as $currentPluginName) {
            if (in_array($currentPluginName, OpenApiDocs::PLUGIN_BLOCKLIST, true)) {
                throw new \RuntimeException('OpenAPI doc generation is blocked for ' . $currentPluginName . '.');
            }
        }

        $pluginDirs = [];
        foreach ($pluginNames as $pluginName) {
            BaseValidator::check('pluginName', $pluginName, [new NotEmpty()]);
            if (!Manager::getInstance()->isPluginInFilesystem($pluginName)) {
                throw new PluginNotFoundException($pluginName);
            }

            $pluginAnnotationsSource = $this->specPathResolver->getAnnotationFilePath($pluginName);
            try {
                $openapi = (new Generator(StaticContainer::get(NullLogger::class)))->generate([
                    $pluginAnnotationsSource,
                ]);
            } catch (\Throwable $e) {
                throw new \Exception('There was an error testing the API annotations for plugin ' . $pluginName, 0, $e);
            }
            if (trim($openapi->toYaml()) === 'openapi: ' . OpenApi::DEFAULT_VERSION) {
                throw new \Exception("The $pluginName plugin's API class does not appear to be annotated yet.");
            }
            $pluginDirs[$pluginName] = $pluginAnnotationsSource;
        }

        $generator = new Generator(StaticContainer::get(LoggerInterface::class));
        $openapi = $generator->setVersion(OpenApi::VERSION_3_1_0)->generate(array_merge([
            $currentPluginDir . '/Annotations/GlobalApiComponents.php',
        ], $pluginDirs));

        $specFileBaseName = 'matomo';
        // If there's only one plugin, name the spec after the plugin
        if (count($pluginNames) === 1) {
            // Update title with plugin name
            $openapi->info->title .= ' for ' . $pluginNames[0] . ' plugin';
            $specFileBaseName = $pluginNames[0];
        }

        $openapi->info->version = $version ?: OpenApiDocs::DEFAULT_SPEC_VERSION;

        // Remove the current server so that it isn't used when saving the spec file. It should only leave demo
        if ($writeToFile && is_array($openapi->servers) && count($openapi->servers) > 1) {
            unset($openapi->servers[0]);
            $openapi->servers = array_values($openapi->servers);
        }

        $lowercaseFormat = strtolower($format);
        $specContents = $lowercaseFormat === 'yaml' ? $openapi->toYaml() : $openapi->toJson();
        if ($writeToFile) {
            $pluginSpecPath = $this->specPathResolver->getSpecFilePath($specFileBaseName, $version, $lowercaseFormat);
            $this->artifactWriter->writeFile($pluginSpecPath, $specContents);
        }

        return $specContents;
    }
}
