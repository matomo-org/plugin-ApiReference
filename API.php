<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Specs\SpecGenerator;

/**
 * API for plugin OpenApiDocs
 *
 * Exposes endpoints to fetch pre-generated OpenAPI specs or generate plugin-specific
 * OpenAPI docs on demand.
 *
 * @method static \Piwik\Plugins\OpenApiDocs\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get a pre-generated OpenAPI spec file if it exists. This endpoint only reads
     * the generated JSON file and does not trigger spec generation.
     *
     * /index.php?module=API&method=OpenApiDocs.getOpenApiSpec&spec=CustomAlerts
     *
     * @param string $spec Plugin name used in the generated filename.
     * @param string $format Output format. Only `json` is supported.
     * @return array<string, mixed> The decoded OpenAPI specification payload.
     * @throws \Exception If the file is missing, unreadable, or contains invalid JSON.
     */
    public function getOpenApiSpec(string $spec, string $format = 'json'): array
    {
        Piwik::checkUserHasSomeViewAccess();

        $this->validateJsonFormat($format);

        if (
            !Manager::getInstance()->isValidPluginName($spec)
            || !Manager::getInstance()->isPluginInFilesystem($spec)
        ) {
            throw new \Piwik\Exception\PluginNotFoundException($spec);
        }

        $filePath = $this->getSpecFilePath($spec);
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

    protected function getSpecFilePath(string $spec): string
    {
        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');

        return $currentPluginDir . OpenApiDocs::GENERATED_SPECS_PATH . $spec . '_openapi_spec_v' . OpenApiDocs::DEFAULT_SPEC_VERSION . '.json';
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

    /**
     * Get the generated API documentation data for the specified plugin.
     *
     * /index.php?module=API&method=OpenApiDocs.getGeneratedOpenApiSpec&plugin=CustomAlerts
     *
     * @param string $plugin Name of the plugin to get the JSON for. E.g. TagManager or CustomerAlerts
     * @param string $format String to indicate JSON or YAML.
     * @return string | array
     * @throws \Exception
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
