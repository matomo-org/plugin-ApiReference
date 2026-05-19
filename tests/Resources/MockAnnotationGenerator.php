<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Resources;

use Piwik\API\DocumentationGenerator;
use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;

class MockAnnotationGenerator extends AnnotationGenerator
{
    public function __construct(DocumentationGenerator $generator)
    {
        parent::__construct($generator);
    }

    public function shouldUseParameterLevelExample(array $typesMap, string $example): bool
    {
        return parent::shouldUseParameterLevelExample($typesMap, $example);
    }

    public function getApplicableDemoExampleUrls(string $pluginName, string $methodName, array $paramsData): array
    {
        return parent::getApplicableDemoExampleUrls($pluginName, $methodName, $paramsData);
    }

    public function getReportMetadataUrl(): string
    {
        return parent::getReportMetadataUrl();
    }

    public function getReportExampleUrlFromMetadata(string $pluginName, string $methodName): string
    {
        return parent::getReportExampleUrlFromMetadata($pluginName, $methodName);
    }

    public function expandTypeAliases(string $type): string
    {
        return parent::expandTypeAliases($type);
    }

    public function parseArrayLikeTypeDefinition(string $type): ?array
    {
        return parent::parseArrayLikeTypeDefinition($type);
    }

    public function setCurrentTypeAliases(array $aliases): void
    {
        $this->currentTypeAliases = $aliases;
    }
}
