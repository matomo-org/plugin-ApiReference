<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\API\DocumentationGenerator;
use Piwik\API\NoDefaultValue;
use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_AnnotationGeneratorTest
 */
class AnnotationGeneratorTest extends TestCase
{
    public const TEST_RESOURCES_DIR = __DIR__ . '/../Resources';

    public const EXAMPLE_API_ENDPOINTS = [
        'API.get',
        'API.getGlossaryMetrics',
        'API.getGlossaryReports',
        'API.getIpFromHeader',
        'API.getMatomoVersion',
        'API.getMetadata',
        'API.getPagesComparisonsDisabledFor',
        'API.getPhpVersion',
        'API.getProcessedReport',
        'API.getReportMetadata',
        'API.getReportPagesMetadata',
        'API.getSegmentsMetadata',
        'API.getSettings',
        'API.getSuggestedValuesForSegment',
        'API.getWidgetMetadata',
        'CustomAlerts.getAlert',
        'CustomAlerts.getAlerts',
        'CustomAlerts.getTriggeredAlerts',
        'CustomDimensions.getAvailableExtractionDimensions',
        'CustomDimensions.getAvailableScopes',
        'CustomDimensions.getConfiguredCustomDimensions',
        'CustomDimensions.getCustomDimension',
        'LogViewer.getAvailableLogReaders',
        'LogViewer.getConfiguredLogReaders',
        'LogViewer.getLogConfig',
        'LogViewer.getLogEntries',
        'MarketingCampaignsReporting.getContent',
        'MarketingCampaignsReporting.getGroup',
        'MarketingCampaignsReporting.getId',
        'MarketingCampaignsReporting.getKeyword',
        'MarketingCampaignsReporting.getMedium',
        'MarketingCampaignsReporting.getName',
        'MarketingCampaignsReporting.getPlacement',
        'MarketingCampaignsReporting.getSource',
        'MarketingCampaignsReporting.getSourceMedium',
    ];

    public const EXAMPLE_RESPONSE_FILE_NAMES = [
        'API.get.json',
        'API.get.tsv',
        'API.get.xml',
        'API.getGlossaryMetrics.json',
        'API.getGlossaryMetrics.tsv',
        'API.getGlossaryMetrics.xml',
        'API.getGlossaryReports.json',
        'API.getGlossaryReports.tsv',
        'API.getGlossaryReports.xml',
        'API.getIpFromHeader.json',
        'API.getIpFromHeader.tsv',
        'API.getIpFromHeader.xml',
        'API.getMatomoVersion.json',
        'API.getMatomoVersion.tsv',
        'API.getMatomoVersion.xml',
        'API.getMetadata.json',
        'API.getMetadata.xml',
        'API.getPagesComparisonsDisabledFor.json',
        'API.getPagesComparisonsDisabledFor.tsv',
        'API.getPagesComparisonsDisabledFor.xml',
        'API.getPhpVersion.json',
        'API.getPhpVersion.tsv',
        'API.getPhpVersion.xml',
        'API.getProcessedReport.json',
        'API.getProcessedReport.xml',
        'API.getReportMetadata.json',
        'API.getReportMetadata.xml',
        'API.getReportPagesMetadata.json',
        'API.getReportPagesMetadata.xml',
        'API.getSegmentsMetadata.json',
        'API.getSegmentsMetadata.xml',
        'API.getSettings.json',
        'API.getSettings.tsv',
        'API.getSettings.xml',
        'API.getSuggestedValuesForSegment.json',
        'API.getSuggestedValuesForSegment.tsv',
        'API.getSuggestedValuesForSegment.xml',
        'API.getWidgetMetadata.json',
        'API.getWidgetMetadata.xml',
        'CustomAlerts.deleteAlert.xml',
        'CustomAlerts.getAlert.json',
        'CustomAlerts.getAlerts.json',
        'CustomAlerts.getAlerts.xml',
        'CustomAlerts.getAlert.xml',
        'CustomAlerts.getTriggeredAlerts.json',
        'CustomAlerts.getTriggeredAlerts.xml',
        'CustomDimensions.getAvailableExtractionDimensions.json',
        'CustomDimensions.getAvailableExtractionDimensions.tsv',
        'CustomDimensions.getAvailableExtractionDimensions.xml',
        'CustomDimensions.getAvailableScopes.json',
        'CustomDimensions.getAvailableScopes.tsv',
        'CustomDimensions.getAvailableScopes.xml',
        'CustomDimensions.getConfiguredCustomDimensions.json',
        'CustomDimensions.getConfiguredCustomDimensions.xml',
        'CustomDimensions.getCustomDimension.json',
        'CustomDimensions.getCustomDimension.tsv',
        'CustomDimensions.getCustomDimension.xml',
        'LogViewer.getAvailableLogReaders.json',
        'LogViewer.getAvailableLogReaders.tsv',
        'LogViewer.getAvailableLogReaders.xml',
        'LogViewer.getConfiguredLogReaders.json',
        'LogViewer.getConfiguredLogReaders.tsv',
        'LogViewer.getConfiguredLogReaders.xml',
        'LogViewer.getLogConfig.json',
        'LogViewer.getLogConfig.xml',
        'LogViewer.getLogEntries.json',
        'LogViewer.getLogEntries.tsv',
        'LogViewer.getLogEntries.xml',
        'MarketingCampaignsReporting.getKeyword.json',
        'MarketingCampaignsReporting.getKeyword.tsv',
        'MarketingCampaignsReporting.getKeyword.xml',
        'MarketingCampaignsReporting.getName.json',
        'MarketingCampaignsReporting.getName.tsv',
        'MarketingCampaignsReporting.getName.xml',
    ];

    public const EXAMPLE_API_METHOD_DOC_BLOCK1 = '/**
     * Copies a specified custom report to one or more sites. If a custom report with the same name already exists, the new custom report
     * will have an automatically adjusted name to make it unique to the assigned site.
     *
     * @param int $idSite
     * @param int $idCustomReport ID of the custom report to duplicate.
     * @param int[] $idDestinationSites Optional array of IDs identifying which site(s) the new custom report is to be
     * assigned to. The default is [idSite] when nothing is provided.
     *
     * @return array
     * @throws Exception
     */';

    /**
     * @var array
     */
    private static $normalisedExamples;

    /**
     * @var array
     */
    private static $truncatedExamples;

    /**
     * @var array
     */
    private static $exampleSchemas;

    /**
     * @var AnnotationGenerator
     */
    private $annotationGenerator;

    public function setUp(): void
    {
        $this->annotationGenerator = new AnnotationGenerator(new DocumentationGenerator());
    }

    /**
     * @param string $apiEndpoint The identifier of the endpoint, like CustomAlerts.getAlert.
     * @param string $format
     *
     * @return string String contents of the raw response body. If the file isn't found, an empty string is returned.
     * @throws \Exception
     */
    private static function getRawExampleResponseForApiEndpoint(string $apiEndpoint, string $format = 'json'): string
    {
        if (!in_array(strtolower($format), ['json', 'xml', 'tsv'])) {
            throw new \Exception('Invalid format: ' . $format . '. Must be: "json", "xml", or "tsv"');
        }

        return file_get_contents(self::TEST_RESOURCES_DIR . "/ExampleResponses/{$apiEndpoint}.{$format}") ?: '';
    }

    /**
     * @param string $plugin
     * @param string $method
     * @param string $format
     *
     * @return string String contents of the raw response body. If the file isn't found, an empty string is returned.
     * @throws \Exception
     */
    private static function getRawExampleResponseForPluginMethod(string $plugin, string $method, string $format = 'json'): string
    {
        return self::getRawExampleResponseForApiEndpoint("{$plugin}.{$method}", $format);
    }

    /**
     * Get the map of example responses which have been normalised in preparation of building schemas.
     *
     * @return array The map of example responses for a bunch of API endpoints.
     * E.g. ['plugin.method' => ['json' => '...', 'xml' => '...', 'tsv' => '...']]
     */
    private static function getNormalisedExamples(): array
    {
        if (empty(self::$normalisedExamples)) {
            $demoExampleResponsesString = file_get_contents(self::TEST_RESOURCES_DIR . '/ExampleResponsesNormalised/ExamplesFromDemoByType.json') ?: '';
            $localExampleResponsesString = file_get_contents(self::TEST_RESOURCES_DIR . '/ExampleResponsesNormalised/ExamplesFromLocalByType.json') ?: '';
            $demoJson = json_decode($demoExampleResponsesString, true) ?? [];
            $localJson = json_decode($localExampleResponsesString, true) ?? [];
            self::$normalisedExamples = array_merge($demoJson, $localJson);
        }

        return self::$normalisedExamples;
    }

    /**
     * Get the map of example responses which have been normalised and truncated.
     *
     * @return array The map of example responses for a bunch of API endpoints.
     * E.g. ['plugin.method' => ['json' => '...', 'xml' => '...', 'tsv' => '...']]
     */
    private static function getTruncatedExamples(bool $onlyTruncated = false): array
    {
        if (empty(self::$truncatedExamples)) {
            $exampleResponsesPostTruncationString = file_get_contents(self::TEST_RESOURCES_DIR . '/ExampleResponsesNormalised/ExamplesPostTruncationByType.json') ?: '';
            self::$truncatedExamples = json_decode($exampleResponsesPostTruncationString, true) ?? [];
        }

        if ($onlyTruncated) {
            return self::$truncatedExamples;
        }

        // Return the normalised examples with any truncated examples overriding them
        return array_merge(self::getNormalisedExamples(), self::$truncatedExamples);
    }

    /**
     * Get the map of example schemas.
     *
     * @return array The map of example schemas for a bunch of API endpoints.
     * E.g. ['plugin.method' => ['json' => '...', 'xml' => '...', 'tsv' => '...']]
     */
    private static function getExampleSchemas(): array
    {
        if (empty(self::$exampleSchemas)) {
            $exampleResponseSchemasString = file_get_contents(self::TEST_RESOURCES_DIR . '/ExampleResponsesNormalised/ExamplesSchemasByType.json') ?: '';
            self::$exampleSchemas = json_decode($exampleResponseSchemasString, true) ?? [];
        }

        return self::$exampleSchemas;
    }

    public function testGeneratePluginApiAnnotations(): void
    {
        // TODO - Test the generatePluginApiAnnotations method
        $this->expectNotToPerformAssertions();
    }

    public function testGetContentForGeneratedAnnotationsFile(): void
    {
        // TODO - getContentForGeneratedAnnotationsFile method
        $this->expectNotToPerformAssertions();
    }

    public function testBuildAnnotationForMethod(): void
    {
        // TODO - buildAnnotationForMethod method
        $this->expectNotToPerformAssertions();
    }

    public function testGetParamInfoFromDocBlock(): void
    {
        // TODO - Update to use resource file and/or dataprovider to test more than one comment block
        $expected = [
            'idSite' => [
                'type' => 'int',
                'description' => '',
                'byRef' => false,
                'variadic' => false,
            ],
            'idCustomReport' => [
                'type' => 'int',
                'description' => 'ID of the custom report to duplicate.',
                'byRef' => false,
                'variadic' => false,
            ],
            'idDestinationSites' => [
                'type' => 'int[]',
                'description' => 'Optional array of IDs identifying which site(s) the new custom report is to be assigned to. The default is [idSite] when nothing is provided.',
                'byRef' => false,
                'variadic' => false,
            ],
        ];
        $this->assertEquals($expected, $this->annotationGenerator->getParamInfoFromDocBlock(self::EXAMPLE_API_METHOD_DOC_BLOCK1));
    }

    public function testGetResponseInfoFromDocBlock(): void
    {
        // TODO - Update to use resource file and/or dataprovider to test more than one comment block
        $expected = [
            'type' => 'array',
        ];
        $this->assertEquals($expected, $this->annotationGenerator->getResponseInfoFromDocBlock(self::EXAMPLE_API_METHOD_DOC_BLOCK1));
    }

    /**
     * @dataProvider getTestDataForBuildVirtualPath
     *
     * @param string $pathTemplate
     * @param string $pluginName
     * @param string $methodName
     * @param string $expected
     *
     * @return void
     */
    public function testBuildVirtualPath(string $pathTemplate, string $pluginName, string $methodName, string $expected): void
    {
        $this->assertEquals($expected, $this->annotationGenerator->buildVirtualPath($pathTemplate, $pluginName, $methodName));
    }

    /**
     * @return iterable<string, string, string, string>
     */
    public static function getTestDataForBuildVirtualPath(): iterable
    {
        yield 'should be empty when all values are empty' => ['', '', '', ''];
        yield 'should be empty when template is empty' => ['', 'SomePlugin', 'SomeMethod', ''];
        yield 'should remain the same when template does not include placeholders' => ['/some/test/path', 'SomePlugin', 'SomeMethod', '/some/test/path'];
        yield 'should replace only plugin when the only placeholder' => ['/{plugin}/test/path', 'SomePlugin', 'SomeMethod', '/SomePlugin/test/path'];
        yield 'should replace only method when the only placeholder' => ['/{method}/test/path', 'SomePlugin', 'SomeMethod', '/SomeMethod/test/path'];
        yield 'should include both values when placeholders are present' => ['/{plugin}/{method}/test/path', 'SomePlugin', 'SomeMethod', '/SomePlugin/SomeMethod/test/path'];
        yield 'should follow placement of placeholders' => ['/{method}/{plugin}/test/path', 'SomePlugin', 'SomeMethod', '/SomeMethod/SomePlugin/test/path'];
        yield 'should allow duplication of placeholders' => ['/{plugin}/{method}/test/path/{plugin}', 'SomePlugin', 'SomeMethod', '/SomePlugin/SomeMethod/test/path/SomePlugin'];
        yield 'should work with query parameter format' => ['/index.php?module=API&method={plugin}.{method}', 'SomePlugin', 'SomeMethod', '/index.php?module=API&method=SomePlugin.SomeMethod'];
        yield 'should work with different names' => ['/index.php?module=API&method={plugin}.{method}', 'TagManager', 'GetContainers', '/index.php?module=API&method=TagManager.GetContainers'];
    }

    /**
     * @dataProvider getTestDataForBuildParameterAnnotationData
     *
     * @param string $paramName
     * @param array $paramMetadata
     * @param array $paramDocInfo
     * @param array $expected
     *
     * @return void
     */
    public function testBuildParameterAnnotationData(string $paramName, array $paramMetadata, array $paramDocInfo, array $expected): void
    {
        $this->assertEquals($expected, $this->annotationGenerator->buildParameterAnnotationData('someMethodName', $paramName, $paramMetadata, $paramDocInfo));
    }

    /**
     * @return iterable<string, array, array, array>
     */
    public function getTestDataForBuildParameterAnnotationData(): iterable
    {
        yield 'should be default values with no data' => ['', [], [], [
            'name' => '',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should be very basic with only param name' => ['someParam', [], [], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should be fine with another param name' => ['idSite', [], [], [
            'name' => 'idSite',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should be still have string type when string is provided' => ['someParam', [
            'type' => 'string',
        ], [], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should be integer type when int is provided' => ['someParam', [
            'type' => 'int',
        ], [], [
            'name' => 'someParam',
            'types' => ['integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should be array type when array is provided' => ['someParam', [
            'type' => 'array',
        ], [], [
            'name' => 'someParam',
            'types' => ['array' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should show as not required and use metadata default when provided' => ['someParam', [
            'default' => 'SomeDefaultValue',
        ], [], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'false',
            'default' => '"SomeDefaultValue"',
            'example' => '',
        ]];
        yield 'should not wrap metadata default value when boolean type' => ['someParam', [
            'type' => 'bool',
            'default' => true,
        ], [], [
            'name' => 'someParam',
            'types' => ['boolean' => null],
            'description' => '',
            'required' => 'false',
            'default' => 'true',
            'example' => '',
        ]];
        yield 'should still count false boolean as a default value' => ['someParam', [
            'type' => 'bool',
            'default' => false,
        ], [], [
            'name' => 'someParam',
            'types' => ['boolean' => null],
            'description' => '',
            'required' => 'false',
            'default' => 'false',
            'example' => '',
        ]];
        yield 'should still count empty string as a default value' => ['someParam', [
            'default' => '',
        ], [], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'false',
            'default' => '""',
            'example' => '',
        ]];
        yield 'should not count the NoDefaultValue class as a default value' => ['someParam', [
            'default' => new NoDefaultValue(),
        ], [], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should not override the metadata type when it is integer' => ['someParam', [
            'type' => 'int',
        ], [
            'type' => 'array',
        ], [
            'name' => 'someParam',
            'types' => ['integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should override the metadata type when it is string and docInfo is array' => ['someParam', [
            'type' => 'string',
        ], [
            'type' => 'array',
        ], [
            'name' => 'someParam',
            'types' => ['array' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should use docInfo type when metadata type is empty' => ['someParam', [], [
            'type' => 'boolean',
        ], [
            'name' => 'someParam',
            'types' => ['boolean' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should determine subtype when docInfo type indicates the type of array items' => ['someParam', [], [
            'type' => 'int[]',
        ], [
            'name' => 'someParam',
            'types' => ['array' => 'integer'],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should still determine subtype when metadata type is array and docInfo indicates subtype' => ['someParam', [
            'type' => 'array',
        ], [
            'type' => 'int[]',
        ], [
            'name' => 'someParam',
            'types' => ['array' => 'integer'],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should allow multiple types when docInfo includes them' => ['someParam', [], [
            'type' => 'string|int|int[]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null, 'array' => 'integer'],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should show one type when docInfo has two types and one is bool' => ['someParam', [], [
            'type' => 'string|bool',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should remove bool type when docInfo has more than 2 types and one is bool' => ['someParam', [], [
            'type' => 'string|int|bool',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should remove bool type regardless of spacing around pipe' => ['someParam', [], [
            'type' => 'string | int | bool',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should remove bool type regardless of spacing and order' => ['someParam', [], [
            'type' => 'bool | string',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should remove bool type even when type hints are wrapped by parenthesis' => ['someParam', [], [
            'type' => '(bool | string)',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should allow multiple types when metadata type is string' => ['someParam', [
            'type' => 'string',
        ], [
            'type' => 'string|int|int[]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null, 'array' => 'integer'],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should allow multiple types when metadata type is bool and doc type is piped' => ['someParam', [
            'type' => 'bool',
        ], [
            'type' => 'string|int|bool',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should allow multiple types when metadata type is bool and doc type is piped even if default is bool' => ['someParam', [
            'type' => 'bool',
            'default' => false,
        ], [
            'type' => 'string|int|bool',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null, 'integer' => null],
            'description' => '',
            'required' => 'false',
            'default' => 'false',
            'example' => '',
        ]];
        yield 'should not allow multiple types when metadata type is specified' => ['someParam', [
            'type' => 'integer',
        ], [
            'type' => 'string|int|int[]',
        ], [
            'name' => 'someParam',
            'types' => ['integer' => null],
            'description' => '',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should use docInfo description when provided' => ['someParam', [], [
            'description' => 'Some test description.',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '',
        ]];
        yield 'should use example when provided using custom syntax in docInfo description' => ['someParam', [], [
            'description' => 'Some test description. [@example=true]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => 'true',
        ]];
        yield 'should use string example when provided using custom syntax in docInfo description' => ['someParam', [], [
            'description' => 'Some test description. [@example="Some test example string."]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => 'Some test example string.',
        ]];
        yield 'should allow full JSON in docInfo description examples' => ['someParam', [], [
            'description' => 'Some test description. [@example={"key1":"value1","key2":"value2"}]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '{"key1":"value1","key2":"value2"}',
        ]];
        yield 'should allow full JSON array in docInfo description examples' => ['someParam', [], [
            'description' => 'Some test description. [@example=[{"key1":"value1","key2":"value2"},{"key3":"value3","key4":"value4"}]]',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '[{"key1":"value1","key2":"value2"},{"key3":"value3","key4":"value4"}]',
        ]];
        yield 'should allow full JSON array examples even when in the middle of the docInfo description' => ['someParam', [], [
            'description' => 'Some test description. [@example=[{"key1":"value1","key2":"value2"},{"key3":"value3","key4":"value4"}]] More test description.',
        ], [
            'name' => 'someParam',
            'types' => ['string' => null],
            'description' => 'Some test description.  More test description.',
            'required' => 'true',
            'default' => 'Piwik\API\NoDefaultValue',
            'example' => '[{"key1":"value1","key2":"value2"},{"key3":"value3","key4":"value4"}]',
        ]];
    }

    public function testDetermineParameters(): void
    {
        // TODO - determineParameters method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestDataForGetOpenApiTypeFromPhpType
     *
     * @param string $type
     * @param string $expected
     * @return void
     */
    public function testGetOpenApiTypeFromPhpType(string $type, string $expected): void
    {
        $this->assertEquals($expected, $this->annotationGenerator->getOpenApiTypeFromPhpType($type));
    }

    /**
     * @return iterable<string, string}>
     */
    public function getTestDataForGetOpenApiTypeFromPhpType(): iterable
    {
        yield 'should be string for empty' => ['', 'string'];
        yield 'should be string for unknown' => ['unknown', 'string'];
        yield 'should be string for abc123' => ['abc123', 'string'];
        yield 'should be array for array' => ['array', 'array'];
        yield 'should be array for []' => ['[]', 'array'];
        yield 'should be array for int[]' => ['int[]', 'array'];
        yield 'should be array for string[]' => ['string[]', 'array'];
        yield 'should be array for bool[]' => ['bool[]', 'array'];
        yield 'should be array for float[]' => ['float[]', 'array'];
        yield 'should be array for double[]' => ['double[]', 'array'];
        yield 'should be integer for int' => ['int', 'integer'];
        yield 'should be integer for integer' => ['integer', 'integer'];
        yield 'should be boolean for bool' => ['bool', 'boolean'];
        yield 'should be boolean for boolean' => ['boolean', 'boolean'];
        yield 'should be number for float' => ['float', 'number'];
        yield 'should be number for double' => ['double', 'number'];
    }

    public function testGetApplicableDemoExampleUrls(): void
    {
        // TODO - getApplicableDemoExampleUrls method
        $this->expectNotToPerformAssertions();
    }

    public function testGetDemoReportMetadata(): void
    {
        // TODO - getDemoReportMetadata method
        $this->expectNotToPerformAssertions();
    }

    public function testGetExampleIfAvailable(): void
    {
        // TODO - getExampleIfAvailable method
        $this->expectNotToPerformAssertions();
    }

    public function testGetReportExampleUrlFromMetadata(): void
    {
        // TODO - getReportExampleUrlFromMetadata method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestXmlExampleObjectData
     *
     * @param string $endpoint
     * @param string $content
     * @param string $expected
     *
     * @return void
     * @throws \Exception
     */
    public function testConvertExampleXmlToObject(string $endpoint, string $content, string $expected): void
    {
        $this->assertNotEmpty($content, 'The example response should not be empty for endpoint: ' . $endpoint);
        $this->assertEquals($expected, json_encode($this->annotationGenerator->convertExampleXmlToObject($content)), "The converted XML was not as expected for endpoint $endpoint.");
    }

    /**
     * @return iterable<string, string, string>
     * @throws \Exception
     */
    public static function getTestXmlExampleObjectData(): iterable
    {
        $normalisedMap = self::getNormalisedExamples();
        foreach (self::EXAMPLE_API_ENDPOINTS as $endpoint) {
            $content = self::getRawExampleResponseForApiEndpoint($endpoint, 'xml');
            $expected = $normalisedMap[$endpoint]['xml'] ?? [];
            yield "converted XML should match expected JSON for $endpoint endpoint" => [$endpoint, $content, $expected];
        }
    }

    public function testDetermineResponses(): void
    {
        // TODO - determineResponses method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestTruncatedExampleObjectData
     *
     * @param string $endpoint
     * @param string $type
     * @param string $normalisedExample
     * @param string $expectedExample
     *
     * @return void
     */
    public function testCutExampleCloseToCharLimit(string $endpoint, string $type, string $normalisedExample, string $expectedExample): void
    {
        $this->assertNotEmpty($normalisedExample, "The example response should not be empty for endpoint '$endpoint' and type '$type'.");
        $result = $this->annotationGenerator->cutExampleCloseToCharLimit($normalisedExample, $type);
        // Add a little wiggle room since the truncation isn't exact and might allow a little over the limit
        $this->assertLessThanOrEqual(AnnotationGenerator::EXAMPLE_CHAR_LIMIT + 30, strlen($result), "The example response should not exceed the character limit for endpoint '$endpoint' and type '$type'.");
        $this->assertEquals($expectedExample, $result, "The truncated example was not as expected for endpoint '$endpoint' and type '$type'.");
    }

    /**
     * @return iterable<string, string, string, string>
     */
    public static function getTestTruncatedExampleObjectData(): iterable
    {
        $truncatedMap = self::getTruncatedExamples();
        $normalisedMap = self::getNormalisedExamples();
        foreach (self::EXAMPLE_API_ENDPOINTS as $endpoint) {
            $normalisedExamples = $normalisedMap[$endpoint] ?? [];

            foreach ($normalisedExamples as $type => $normalisedExample) {
                $expectedExample = $normalisedExample;
                if (!empty($truncatedMap[$endpoint][$type])) {
                    $expectedExample = $truncatedMap[$endpoint][$type];
                }

                // Skip the endpoints which don't have TSV examples
                if (
                    (
                        $type === 'tsv'
                        && in_array($endpoint, [
                            'CustomAlerts.getAlert',
                            'CustomAlerts.getAlerts',
                            'CustomAlerts.getTriggeredAlerts',
                            'CustomDimensions.getConfiguredCustomDimensions',
                            'LogViewer.getLogConfig',
                        ])
                    )
                ) {
                    continue;
                }

                yield "truncated example should match expected JSON for $endpoint endpoint and $type type" => [$endpoint, $type, $normalisedExample, $expectedExample];
            }
        }
    }

    public function testBuildSchemaAnnotationFromJsonExample(): void
    {
        // TODO - buildSchemaAnnotationFromJsonExample method
        $this->expectNotToPerformAssertions();
    }

    public function testBuildPropertyAnnotationFromJsonExample(): void
    {
        // TODO - buildPropertyAnnotationFromJsonExample method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestXmlSchemaData
     *
     * @param string $endpoint
     * @param array $normalisedObject
     * @param array $expected
     *
     * @return void
     */
    public function testBuildSchemaAnnotationFromXmlExample(string $endpoint, array $normalisedObject, array $expected): void
    {
        $this->assertNotEmpty($normalisedObject, 'The decoded example response should not be empty for endpoint: ' . $endpoint);
        $result = $this->annotationGenerator->buildSchemaAnnotationFromXmlExample($normalisedObject);
        $this->assertEquals($expected, $result, "The XML schema was not as expected for endpoint $endpoint.");
    }

    /**
     * @return iterable<string, array, array>
     */
    public static function getTestXmlSchemaData(): iterable
    {
        $normalisedMap = self::getNormalisedExamples();
        $schemasMap = self::getExampleSchemas();
        foreach (self::EXAMPLE_API_ENDPOINTS as $endpoint) {
            $normalisedString = $normalisedMap[$endpoint]['xml'] ?? '';
            $normalisedObject = json_decode($normalisedString, true) ?? [];
            $expected = json_decode($schemasMap[$endpoint]['xml'] ?? '', true) ?? [];
            yield "should match expected XML schema for $endpoint endpoint" => [$endpoint, $normalisedObject, $expected];
        }
    }

    public function testBuildPropertyAnnotationFromXmlExample(): void
    {
        // TODO - buildPropertyAnnotationFromXmlExample method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestDataForRemoveTrailingCommaFromLastLine
     *
     * @param array $lines
     * @param array $expected
     *
     * @return void
     */
    public function testRemoveTrailingCommaFromLastLine(array $lines, array $expected): void
    {
        $this->annotationGenerator->removeTrailingCommaFromLastLine($lines);
        $this->assertEquals($expected, $lines);
    }

    /**
     * @return iterable<array, array>
     */
    public function getTestDataForRemoveTrailingCommaFromLastLine(): iterable
    {
        yield 'should be fine with empty arrays' => [[], []];
        yield 'should be fine with no commas' => [['test'], ['test']];
        yield 'should be fine with multiple lines and no commas' => [['test1', 'test2'], ['test1', 'test2']];
        yield 'should remove the trailing comma from the last line' => [['test1,', 'test2,'], ['test1,', 'test2']];
        yield 'should only remove the trailing comma from the last line' => [['test1,test2,test3,', 'test4,test5,test6,'], ['test1,test2,test3,', 'test4,test5,test6']];
        yield 'should handle spaced lists correctly' => [['test1, test2, test3,', 'test4, test5, test6,'], ['test1, test2, test3,', 'test4, test5, test6']];
        yield 'should only remove the comma if it is the last character' => [['test1, test2, test3,', 'test4, test5, test6, '], ['test1, test2, test3,', 'test4, test5, test6, ']];
        yield 'should handle nested JSON correctly' => [
            [
                '@OA\Property(',
                '    property="dimensions",',
                '    type="object",',
                '    @OA\Property(',
                '        property="row",',
                '        type="array",',
                '        @OA\Items(',
                '            type="string"',
                '        )',
                '    )',
                '),',
            ],
            [
                '@OA\Property(',
                '    property="dimensions",',
                '    type="object",',
                '    @OA\Property(',
                '        property="row",',
                '        type="array",',
                '        @OA\Items(',
                '            type="string"',
                '        )',
                '    )',
                ')',
            ],
        ];
    }

    public function testBuildLinesForAnnotationObject(): void
    {
        // TODO - buildLinesForAnnotationObject method
        $this->expectNotToPerformAssertions();
    }

    public function testBuildSchemaObjectArray(): void
    {
        // TODO - buildSchemaObjectArray method
        $this->expectNotToPerformAssertions();
    }

    /**
     * @dataProvider getTestDataForWrapStringWithQuotes
     *
     * @param string $stringValue
     * @param string $type
     * @param string|null $quoteCharacter
     *
     * @return void
     */
    public function testWrapStringWithQuotes(string $stringValue, string $type, ?string $quoteCharacter, string $expected): void
    {
        $result = $quoteCharacter === null ? $this->annotationGenerator->wrapStringWithQuotes($stringValue, $type) : $this->annotationGenerator->wrapStringWithQuotes($stringValue, $type, $quoteCharacter);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return iterable<string, string, ?string, string>
     */
    public function getTestDataForWrapStringWithQuotes(): iterable
    {
        yield 'should be empty quoted string if everything is empty' => ['', '', null, '""'];
        yield 'should be empty quoted string if string type and empty value' => ['', 'string', null, '""'];
        yield 'should be empty string if integer type and empty value' => ['', 'integer', null, ''];
        yield 'should be empty string if boolean type and empty value' => ['', 'boolean', null, ''];
        yield 'should be empty string if array type and empty value' => ['', 'array', null, ''];
        yield 'should be empty quoted string if string type and quoted empty string value' => ['""', 'string', null, '""'];
        yield 'should be empty single-quoted string if string type and single-quoted empty string value' => ["''", 'string', null, "''"];
        yield 'should be empty object string if string type and empty object value' => ['{}', 'string', null, '{}'];
        yield 'should be use the quote character when provided' => ["''", '', '"', "''"];
        yield 'should be use the quote character when provided even when not quote' => ['', '', '|', '||'];
        yield 'should be quoted string if no type and string value' => ['test', '', null, '"test"'];
        yield 'should be quoted string if string type and string value' => ['test', 'string', null, '"test"'];
        yield 'should be integer string if integer type and integer string value' => ['12', 'integer', null, '12'];
        yield 'should be boolean string if boolean type and boolean string value' => ['true', 'boolean', null, 'true'];
        yield 'should be array string if array type and array string value' => ['[]', 'array', null, '[]'];
        yield 'should be use the custom quote character when provided even when not quote' => ['test', 'string', '|', "|test|"];
        yield 'should ignore the custom quote character when integer type' => ['30', 'integer', '|', '30'];
        yield 'should ignore the custom quote character when boolean type' => ['30', 'boolean', '|', '30'];
        yield 'should ignore the custom quote character when array type' => ['30', 'array', '|', '30'];
    }

    public function testShouldIncludeDefault(): void
    {
        // TODO - shouldIncludeDefault method
        $this->expectNotToPerformAssertions();
    }

    public function testBuildSchemaObjectArrays(): void
    {
        // TODO - buildSchemaObjectArrays method
        $this->expectNotToPerformAssertions();
    }

    public function testCompileOperationLines(): void
    {
        // TODO - compileOperationLines method
        $this->expectNotToPerformAssertions();
    }
}
