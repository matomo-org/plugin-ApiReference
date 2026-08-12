<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Resources;

use Piwik\API\DocumentationGenerator;
use Piwik\Plugins\ApiReference\Annotations\AnnotationGenerator;

class MockAnnotationGenerator extends AnnotationGenerator
{
    public function __construct(DocumentationGenerator $generator)
    {
        parent::__construct($generator);

        // TODO - Extend the constructor behaviour
    }

    // TODO - Refactor the methods below to use dependency injection so that they can more easily be tested

    /**
     * @inheritDoc
     */
    public function buildAnnotationForMethod(array $rules, string $pluginName, \ReflectionMethod $reflectionMethod): array
    {
        return parent::buildAnnotationForMethod($rules, $pluginName, $reflectionMethod);
    }

    /**
     * @inheritDoc
     */
    public function determineParameters(array $rules, string $plugin, string $method, \ReflectionMethod $reflectionMethod): array
    {
        return parent::determineParameters($rules, $plugin, $method, $reflectionMethod);
    }

    /**
     * @inheritDoc
     */
    public function getApplicableDemoExampleUrls(string $pluginName, string $methodName, array $paramsData): array
    {
        return parent::getApplicableDemoExampleUrls($pluginName, $methodName, $paramsData);
    }

    /**
     * @inheritDoc
     */
    public function getDemoReportMetadata(): array
    {
        return parent::getDemoReportMetadata();
    }

    /**
     * @inheritDoc
     */
    public function getExampleIfAvailable(string $url, bool $ignoreCached = false): string
    {
        return parent::getExampleIfAvailable($url, $ignoreCached);
    }

    /**
     * @inheritDoc
     */
    public function getReportExampleUrlFromMetadata(string $pluginName, string $methodName): string
    {
        return parent::getReportExampleUrlFromMetadata($pluginName, $methodName);
    }

    public function getReportMetadataUrl(): string
    {
        return parent::getReportMetadataUrl();
    }

    public function prependInstanceUrl(string $path): string
    {
        return parent::prependInstanceUrl($path);
    }

    /**
     * @inheritDoc
     */
    public function determineResponses(array $rules, string $plugin, string $method, \ReflectionMethod $reflectionMethod, array $paramsData): array
    {
        return parent::determineResponses($rules, $plugin, $method, $reflectionMethod, $paramsData);
    }

    public function normaliseConfiguredParameterExample($example, array $typesMap = []): ?string
    {
        return parent::normaliseConfiguredParameterExample($example, $typesMap);
    }

    public function isBasicExampleArray(array $example): bool
    {
        return parent::isBasicExampleArray($example);
    }

    public function supportsBasicArrayExample(array $typesMap): bool
    {
        return parent::supportsBasicArrayExample($typesMap);
    }

    public function shouldUseParameterLevelExample(array $typesMap, string $example): bool
    {
        return parent::shouldUseParameterLevelExample($typesMap, $example);
    }

    public function shouldAcceptInvalidSslCertificate(): bool
    {
        return parent::shouldAcceptInvalidSslCertificate();
    }

    public function isReadOnlyApiMethod(string $pluginName, string $methodName): bool
    {
        return parent::isReadOnlyApiMethod($pluginName, $methodName);
    }
}
