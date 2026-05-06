<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

use Piwik\Piwik;
use Piwik\Plugins\OpenApiDocs\Generation\PluginListProvider;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Specs\SpecGenerator;
use Piwik\Plugins\OpenApiDocs\Specs\PathResolver;

/**
 * Provides Reporting API endpoints for reading OpenAPI plugin configuration and specifications.
 *
 * Exposes endpoints to return the effective plugin list for spec generation, read pre-generated spec files,
 * or generate plugin OpenAPI specifications on demand.
 *
 * @method static \Piwik\Plugins\OpenApiDocs\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the plugin names used for OpenApiDocs spec generation.
     *
     * @return array<int, string>
     */
    public function getAllowedPlugins(): array
    {
        Piwik::checkUserHasSomeViewAccess();

        return $this->getPluginListProvider()->getAllowedPlugins();
    }

    /**
     * Returns a previously generated OpenAPI specification for a plugin.
     *
     * Reads the stored JSON spec file for the requested plugin and does not trigger
     * spec generation.
     *
     * @param string $pluginName The plugin name whose generated OpenAPI spec file should be read.
     * @param string $format The response format to read. Only `json` is supported.
     * @return array<string, mixed> The decoded OpenAPI specification data from the generated JSON file.
     */
    public function getOpenApiSpec(string $pluginName, string $format = 'json'): array
    {
        Piwik::checkUserHasSomeViewAccess();

        $this->validateJsonFormat($format);

        if (
            !Manager::getInstance()->isValidPluginName($pluginName)
            || !Manager::getInstance()->isPluginInFilesystem($pluginName)
        ) {
            throw new \Exception('Invalid plugin name: ' . $pluginName);
        }

        $filePath = $this->getSpecFilePath($pluginName);
        if (!$this->isSpecFileReadable($filePath)) {
            throw new \Exception('OpenAPI spec file was not found. Generate it first via openapidocs:generate-spec-file.');
        }

        $specContents = $this->readSpecFile($filePath);
        if ($specContents === false) {
            throw new \Exception('OpenAPI spec file could not be read.');
        }

        $decodedSpec = json_decode($specContents, true);
        if (!is_array($decodedSpec) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('OpenAPI spec file contains invalid JSON.');
        }

        return $decodedSpec;
    }

    protected function getSpecFilePath(string $pluginName): string
    {
        return $this->getSpecPathResolver()->getSpecFilePath($pluginName);
    }

    protected function isSpecFileReadable(string $filePath): bool
    {
        return is_file($filePath) && is_readable($filePath);
    }

    /**
     * @param string $filePath
     * @return string|false
     */
    protected function readSpecFile(string $filePath)
    {
        return file_get_contents($filePath);
    }

    protected function validateJsonFormat(string $format): void
    {
        if (strtolower($format) !== 'json') {
            throw new \Exception(
                Piwik::translate(
                    'General_ExceptionInvalidReportRendererFormat',
                    [$format, 'json']
                )
            );
        }
    }

    protected function getSpecPathResolver(): PathResolver
    {
        return new PathResolver();
    }

    protected function getPluginListProvider(): PluginListProvider
    {
        return new PluginListProvider();
    }

    /**
     * Generates an OpenAPI specification for one or more plugins and returns it immediately.
     *
     * @param string $plugin The plugin name to generate, or a comma-separated list of plugin names.
     * @param string $format The response format to generate. Supported values are `json` and `yaml`.
     * @return array<string, mixed>|string The generated OpenAPI specification as decoded JSON data for
     *                                     `json`, or as a YAML string for `yaml`.
     */
    public function getGeneratedOpenApiSpec(string $plugin, string $format)
    {
        Piwik::checkUserHasSomeViewAccess();

        // Return an error if format is something other than JSON or YAML
        $allowedFormats = ['json', 'yaml'];
        if (!in_array(strtolower($format), $allowedFormats)) {
            throw new \Exception(
                Piwik::translate(
                    'General_ExceptionInvalidReportRendererFormat',
                    [$format, implode(', ', $allowedFormats)]
                )
            );
        }

        $docString = (new SpecGenerator())->generatePluginDoc($plugin, $format);
        return strtolower($format) === 'json' ? json_decode($docString, true) : $docString;
    }
}
