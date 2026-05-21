<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ApiReference;

use Piwik\Piwik;
use Piwik\Plugins\ApiReference\Generation\PluginListProvider;
use Piwik\Plugin\Manager;
use Piwik\Plugins\ApiReference\Specs\PathResolver;

/**
 * Provides Reporting API endpoints for reading OpenAPI plugin configuration and specifications.
 *
 * Exposes endpoints to return the effective plugin list for spec generation, read pre-generated spec files,
 * or generate plugin OpenAPI specifications on demand.
 *
 * @method static \Piwik\Plugins\ApiReference\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    private const TRY_IT_OUT_NOTE_TRANSLATION_KEY = 'ApiReference_UseTryItOutForLiveResponse';

    /**
     * Returns the plugin names used for ApiReference spec generation.
     *
     * @return array<int, string>
     */
    public function getAllowedPlugins(): array
    {
        Piwik::checkUserHasSomeViewAccess();

        return $this->getPluginListProvider()->getAllowedPlugins();
    }

    /**
     * Returns metadata for the plugins used by ApiReference spec generation.
     *
     * @return array<string, array{description: string}>
     */
    public function getAllowedPluginMetadata(): array
    {
        Piwik::checkUserHasSomeViewAccess();

        return $this->getPluginListProvider()->getAllowedPluginMetadata();
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
            throw new \Exception('OpenAPI spec file was not found. Generate it first via apireference:generate-spec-file.');
        }

        $specContents = $this->readSpecFile($filePath);
        if ($specContents === false) {
            throw new \Exception('OpenAPI spec file could not be read.');
        }

        $decodedSpec = json_decode($specContents, true);
        if (!is_array($decodedSpec) || json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('OpenAPI spec file contains invalid JSON.');
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            $decodedSpec = $this->removeSuccessfulResponseExamples($decodedSpec);
        }

        return $decodedSpec;
    }

    /**
     * Remove embedded example payloads from successful 200 responses.
     *
     * @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    protected function removeSuccessfulResponseExamples(array $spec): array
    {
        if (empty($spec['paths']) || !is_array($spec['paths'])) {
            return $spec;
        }

        foreach ($spec['paths'] as &$pathItem) {
            if (!is_array($pathItem)) {
                continue;
            }

            foreach ($pathItem as &$operation) {
                if (!is_array($operation)) {
                    continue;
                }

                $this->sanitizeSuccessfulResponse($operation);
            }
            unset($operation);
        }
        unset($pathItem);

        return $spec;
    }

    /**
     * Remove examples from a successful 200 response and append the try-it-out note once.
     *
     * @param array<string, mixed> $operation
     */
    protected function sanitizeSuccessfulResponse(array &$operation): void
    {
        if (empty($operation['responses']) || !is_array($operation['responses'])) {
            return;
        }

        if (!isset($operation['responses']['200']) || !is_array($operation['responses']['200'])) {
            return;
        }

        $successfulResponse = &$operation['responses']['200'];

        if (
            !empty($successfulResponse['description'])
            && is_string($successfulResponse['description'])
            && strpos($successfulResponse['description'], $this->getTryItOutNote()) === false
        ) {
            $successfulResponse['description'] .= $this->getTryItOutNote();
        }

        if (empty($successfulResponse['content']) || !is_array($successfulResponse['content'])) {
            return;
        }

        foreach ($successfulResponse['content'] as &$content) {
            if (is_array($content)) {
                unset($content['example'], $content['examples'], $content['schema']);
            }
        }
        unset($content);
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

    protected function getTryItOutNote(): string
    {
        return "\n\n" . Piwik::translate(self::TRY_IT_OUT_NOTE_TRANSLATION_KEY);
    }

    protected function getSpecPathResolver(): PathResolver
    {
        return new PathResolver();
    }

    protected function getPluginListProvider(): PluginListProvider
    {
        return new PluginListProvider();
    }

}
