<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Annotations;

use Piwik\API\DocumentationGenerator;
use Piwik\API\NoDefaultValue;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Http;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;

class AnnotationGenerator
{
    public const EXAMPLE_CHAR_LIMIT = 3000;

    /**
     * @var DocumentationGenerator
     */
    protected $generator;

    /**
     * @var array[]
     */
    protected $reportMetadata;

    public function __construct(DocumentationGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Generate all the annotations for a plugin's public API endpoints and return them as an array of strings. A string
     * for each line to be output or written to file.
     *
     * @param string $pluginName The name of the plugin. E.g. TagManager
     * @param bool $writeToFile Indicate whether the results should be written to file. Default is false so that a dry
     * run won't affect the file-system.
     * @param bool $useTmpDir Indicate whether the file should be written in Matomo's tmp/ directory. The default is
     * false, meaning that it will be written in the OpenApi/Annotations/ directory of the plugin, creating the
     * directory if it doesn't already exist. This is useful if we just want a temp file for comparison. like during
     * testing.
     *
     * @return string[]|array[] The collection of all the lines which make up the generated annotations for the public API
     * endpoints defined by the plugin.
     * @throws \Piwik\Exception\PluginDeactivatedException If the plugin is not activated. It should be loaded.
     * @throws \Throwable
     */
    public function generatePluginApiAnnotations(string $pluginName, bool $writeToFile = false, bool $useTmpDir = false): array
    {
        BaseValidator::check('plugin', $pluginName, [ new NotEmpty() ]);
        Manager::getInstance()->checkIsPluginActivated($pluginName);

        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $rules = require $currentPluginDir . '/Annotations/config.php';
        $pluginDir = Manager::getInstance()::getPluginDirectory($pluginName);
        $pluginAnnotationDir = !$useTmpDir ? $pluginDir . '/OpenApi/Annotations' : PIWIK_INCLUDE_PATH . '/tmp/OpenApi/Annotations';
        $pluginAnnotationPath = $pluginAnnotationDir . '/GeneratedAnnotations.php';
        // If the directory doesn't exist yet, create it
        if ($writeToFile && !is_dir($pluginAnnotationDir)) {
            mkdir($pluginAnnotationDir, 0777, true);
        }

        $className = Request::getClassNameAPI($pluginName);

        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return [];
        }

        Proxy::getInstance()->registerClass($className);
        $pluginMetadata = Proxy::getInstance()->getMetadata()[$className] ?? [];

        $annotations = [];
        foreach (array_keys($pluginMetadata) as $metadataMethod) {
            if (!$reflectionClass->hasMethod($metadataMethod)) {
                continue;
            }

            $methodAnnotations = $this->buildAnnotationForMethod($rules, $pluginName, $reflectionClass->getMethod($metadataMethod));
            if (empty($methodAnnotations)) {
                continue;
            }

            $annotations[] = $methodAnnotations;
        }

        if ($writeToFile) {
            $this->writeAnnotationsToFile($annotations, $pluginAnnotationPath, $pluginName);
        }

        return $annotations;
    }

    /**
     * Write the collection of annotation lines to file, overwriting the file if it already exists.
     *
     * @param array[] $annotations Collection of generated annotations. It's an array of arrays containing the lines
     * which make up all the annotations which need to be written to file.
     * @param string $filePath Full path of the file to be overwritten with the annotations.
     * @param string $pluginName Name of the plugin. E.g. TagManager
     *
     * @return false|int Indicating how much was written to file.
     * @see file_put_contents To explain the return value.
     */
    public function writeAnnotationsToFile(array $annotations, string $filePath, string $pluginName)
    {
        $lines = [
            '<?php',
            '',
            'namespace Piwik\\Plugins\\' . $pluginName . '\\OpenApi\\Annotations;',
            '',
            '/**',
        ];

        foreach ($annotations as $annotation) {
            foreach ($annotation as $line) {
                $lines[] = ' * ' . $line;
            }
        }

        $lines = array_merge($lines, [
            ' */',
            'class GeneratedAnnotations',
            '{',
            '',
            '}',
        ]);

        // Create or overwrite the annotations file
        return file_put_contents($filePath, implode(PHP_EOL, $lines));
    }

    /**
     * Build the full array of lines for an OA operation, like OA\Get or OA\Post. This pulls data from various sources,
     * including making API calls to get example responses.
     *
     * @param array $rules An array of configs determining which responses to include by default.
     * @param string $pluginName Name of the plugin. E.g. TagManager.
     * @param \ReflectionMethod $reflectionMethod The reflective representation of the method to provide metadata.
     *
     * @return array
     * @throws \Throwable
     */
    protected function buildAnnotationForMethod(array $rules, string $pluginName, \ReflectionMethod $reflectionMethod): array
    {
        $existing = $reflectionMethod->getDocComment();
        // Skip methods which have been marked as internal or auto annotations disabled
        if (
            $existing !== false && (stripos($existing, 'OA-AUTO:OFF') !== false
                || stripos($existing, '@internal') !== false
                || stripos($existing, '@hide') !== false)
        ) {
            return [];
        }

        $methodName = $reflectionMethod->getName();
        $opId = Proxy::getInstance()->buildApiActionName($pluginName, $methodName);
        $path = $this->buildVirtualPath(
            $rules['virtualPathTemplate'] ?? '/' . $opId,
            $pluginName,
            $methodName
        );

        $params = $this->determineParameters($rules, $pluginName, $methodName, $reflectionMethod);
        $responses = $this->determineResponses($rules, $pluginName, $methodName, $reflectionMethod, $params);

        $isPost = !empty($rules['plugins'][$pluginName]['methodsRequiringPost'])
            && in_array($methodName, $rules['plugins'][$pluginName]['methodsRequiringPost']);

        return $this->compileOperationLines($path, $opId, $pluginName, $params, $responses, $isPost);
    }

    /**
     * Try to extract the list of parameters and key information about them from the method's doc block string.
     *
     * @param string $docBlock The comment block from a method, which hopefully contains the param annotations.
     *
     * @return array Of each param provided in the comment block and key information about them like the type and
     * description, if available. The array can be empty if there are no param annotations present. E.g.
     * ['idSite' => ['type' => 'integer', 'description' => 'Site ID'], 'date' => ['type' => 'string', 'description' => '']]
     */
    public function getParamInfoFromDocBlock(string $docBlock): array
    {
        $lexer  = new Lexer();
        $tokens = $lexer->tokenize($docBlock);
        $expressionParser = new ConstExprParser();
        $parser = new PhpDocParser(new TypeParser($expressionParser), $expressionParser);
        $node   = $parser->parse(new TokenIterator($tokens));

        $params = [];
        foreach ($node->getParamTagValues() as $param) {
            $name = ltrim($param->parameterName, '$');
            $params[$name] = [
                'type' => (string) $param->type,
                 // Normalise the description. E.g. remove linebreaks and indentation
                'description' => trim(preg_replace(['/^\h+/m', '/\R+/u',], ['', ' '], $param->description)),
                'byRef' => $param->isReference,
                'variadic' => $param->isVariadic,
            ];
        }
        return $params;
    }

    /**
     * Try to extract the response-type of a method from the doc block string.
     *
     * @param string $docBlock The comment block from a method, which hopefully contains the return annotation.
     *
     * @return array The collection of key information about the method's return type if any is found.
     * E.g. ['type' => 'integer', 'description' => 'The ID of the newly created report.'] or ['type' => null] if no
     * return annotation is present.
     */
    public function getResponseInfoFromDocBlock(string $docBlock): array
    {
        $lexer  = new Lexer();
        $tokens = $lexer->tokenize($docBlock);
        $expressionParser = new ConstExprParser();
        $parser = new PhpDocParser(new TypeParser($expressionParser), $expressionParser);
        $node   = $parser->parse(new TokenIterator($tokens));

        $responseInfo = ['type' => null];
        $returnTags = $node->getReturnTagValues();
        if (empty($returnTags)) {
            return $responseInfo;
        }

        $returnTag = $returnTags[0];
        $tagValue = strval($returnTag->type);
        $responseInfo['type'] = $this->getOpenApiTypeFromPhpType($tagValue);
        if ($responseInfo['type'] === 'string' && !empty($tagValue) && strtolower($tagValue) !== 'string') {
            $responseInfo['type'] = '';
            $responseInfo['description'] = 'Response of unknown type';
        }
        if (!empty($returnTag->description)) {
            $responseInfo['description'] = $returnTag->description;
        }

        return $responseInfo;
    }

    /**
     * This is a helper method for building the path used for an operation annotation. It takes a path template, like
     * the one from the config array and populates it with the plugin name and API method name.
     *
     * @param string $virtualPathTemplate The template of what the path should be.
     * E.g. /index.php?module=API&method={plugin}.{method}
     * @param string $plugin The name of the plugin. E.g. TagManager
     * @param string $method The name of the API method. E.g. getCustomReport
     *
     * @return string The finalised path to be used in an operation annotation.
     * E.g. /index.php?module=API&method=CustomReports.getConfiguredReport
     */
    public function buildVirtualPath(string $virtualPathTemplate, string $plugin, string $method): string
    {
        return str_replace(['{plugin}', '{method}'], [$plugin, $method], $virtualPathTemplate);
    }

    /**
     * Build the key data for the specified parameter. This should be all the data necessary to create an OA\Parameter
     * annotation object.
     *
     * @param string $paramName The name of the parameter. E.g. idSite or period
     * @param array $paramMetadata The collection of metadata from the old DocumentationGenerator class. Things like
     * whether the parameter is typed, is required, or has a default value.
     * @param array $paramDocInfo The collection of parameter information built from the method doc block. This is
     * especially useful when the metadata wasn't able to determine the type. We can check the param annotation for the
     * type and description.
     *
     * @return array The array of key information about the parameter like the type (types if more than one is hinted),
     * the name, whether it's required, default value, and example. Since there may be more than one type from the doc
     * block, the type is specified as a 'types' array even if there's only one type. E.g.
     * [
     *     'name' => 'idSite',
     *     'types' => ['integer', 'string'],
     *     'description' => 'The ID of the site.',
     *     'required' => 'true', // It's a string here, but gets converted to boolean in the annotation.
     *     'default' => '\Piwik\API\NoDefaultValue', // This class name indicates no default value since falsy values might be valid.
     *     'example' => 1,
     * ]
     */
    public function buildParameterAnnotationData(string $paramName, array $paramMetadata, array $paramDocInfo): array
    {
        $docType = strtolower(trim($paramDocInfo['type'] ?? ''));
        $metaType = strtolower(trim($paramMetadata['type'] ?? $docType));
        $type = $metaType === 'string' && $docType !== 'string' ? $docType : $metaType;
        // If the signature type is array, but the type hinting provides more, use that instead
        if ($type === 'array' && strpos($docType, '[]') !== false && strpos($docType, '|') === false) {
            $type = $docType;
        }
        $typesMap = [];
        // Check for pipes and try to list possible types
        foreach (explode('|', $type) as $typePart) {
            $typePart = trim($typePart, ' ()');
            $normalisedType = $this->getOpenApiTypeFromPhpType($typePart);
            // If the type is array, check if there's a subType
            $subType = null;
            if ($normalisedType === 'array' && $typePart !== 'array' && strpos($typePart, '[]') !== false) {
                $subType = substr($typePart, 0, strpos($typePart, '[]'));
            }
            $typesMap[$normalisedType] = $subType !== null ? $this->getOpenApiTypeFromPhpType($subType) : $subType;
        }

        $isRequired = !key_exists('default', $paramMetadata) || $paramMetadata['default'] instanceof NoDefaultValue;
        $description = $paramDocInfo['description'] ?? '';
        $example = '';
        // Check the description for the example value
        if (preg_match('/\[@example\s*=\s*([^\n]+)\]/', $description, $m)) {
            if ($m[1] !== '') {
                $example = $m[1];
            }
            // Remove the example from the description and trim any excess whitespace
            $description = trim(str_replace($m[0], '', $description));
            // Trim any excess whitespace and surrounding quotes from the example
            $example = trim($example);
            $example = trim($example, '"');
        }

        return [
            'name' => $paramName,
            'types' => $typesMap,
            'description' => $description,
            'required' => $isRequired ? 'true' : 'false',
            'default' => !$isRequired ? json_encode($paramMetadata['default']) : NoDefaultValue::class,
            'example' => $example,
        ];
    }

    /**
     * Build the collection of parameters and key information about them for the specified method.
     *
     * @param array $rules An array of configs determining which responses to include by default.
     * @param string $plugin Name of the plugin. E.g. TagManager.
     * @param string $method The name of the method being annotated.
     * @param \ReflectionMethod $reflectionMethod The reflective representation of the method to provide metadata.
     *
     * @return array List of each method parameter and key data points like the data type, whether it's required,
     * default value, and example value.
     * @see self::buildParameterAnnotationData() where the parameter data is built.
     */
    protected function determineParameters(array $rules, string $plugin, string $method, \ReflectionMethod $reflectionMethod): array
    {
        $refs = [];

        if (!empty($rules['defaultParamRefs'])) {
            $refs = array_merge($refs, $rules['defaultParamRefs']);
        }

        if (isset($rules['plugins'][$plugin]['paramRefsByMethod'][$method])) {
            $refs = array_merge($refs, $rules['plugins'][$plugin]['paramRefsByMethod'][$method]);
        }

        $paramsMetadata = Proxy::getInstance()->getParametersListWithTypes(Request::getClassNameAPI($plugin), $method);
        $paramsInfo = $this->getParamInfoFromDocBlock($reflectionMethod->getDocComment());

        $customParams = [];
        foreach ($paramsMetadata as $name => $paramMetadata) {
            $paramInfo = $paramsInfo[$name] ?? [];
            // Skip references and variadic for now
            // TODO - determine whether these can be handled automatically or if they have to be manual
            if (!empty($paramInfo['byRef']) || !empty($paramInfo['variadic'])) {
                continue;
            }

            $customParams[] = $this->buildParameterAnnotationData($name, $paramMetadata, $paramInfo);
        }

        return [
            'refs' => array_values(array_unique($refs)),
            'custom' => $customParams,
        ];
    }

    /**
     * Map the PHP type to the OpenAPI type. The currently available types for v3.1.1 are the following: “null”,
     * “boolean”, “object”, “array”, “number”, “string”, or “integer”.
     *
     * @link https://spec.openapis.org/oas/v3.1.1.html#data-types
     *
     * @param string $type The PHP type from the method signature or doc-block
     * @return string The normalised Data Type to be used in the swagger-php annotation
     */
    public function getOpenApiTypeFromPhpType(string $type): string
    {
        // TODO - Is there a good way to handle object type or should that always be ref?
        // TODO - Eventually handle the Data Type Formats: https://spec.openapis.org/oas/v3.1.1.html#data-type-format
        switch (strtolower($type)) {
            case 'array':
            case '[]':
            case 'int[]':
            case 'string[]':
            case 'bool[]':
            case 'float[]':
            case 'double[]':
                $type = 'array';
                break;
            case 'int':
            case 'integer':
                $type = 'integer';
                break;
            case 'bool':
            case 'boolean':
                $type = 'boolean';
                break;
            case 'float':
            case 'double':
                $type = 'number';
                break;
            default:
                $type = 'string';
        }

        return $type;
    }

    /**
     * Try to build example URLs for a specific API method. This uses the old DocumentationGenerator to build the same
     * example URLs which have been available on the API documentation page for a long time. E.g. XML, JSON, and TSV.
     * Unlike the old documentation, this only includes URLs if a valid response was received from the demo server or
     * local Matomo instance. For example, some endpoints respond that the data structure is not TSV compatible and the
     * old documentation would still include the link. This shows 'TSV (N/A)' in those instances.
     *
     * @param string $pluginName The name of the plugin. E.g. TagManager.
     * @param string $methodName The name of the plugin specific API method. E.g. getCustomReport.
     * @param array[] $paramsData The collection of parameter data compiled using reflection and metadata. This includes
     * types, default values, and examples. It can be used to build URLs using required parameters which aren't globals,
     * like idSite and period which have established example values.
     *
     * @return array The example URLs with only the required query parameters and only if a valid example responses were
     * received when the URL was queried. Empty string if no URL could be determined or no valid response was received.
     * E.g. ['xml => 'https://demo...&format=xml', 'json' => 'https://demo...&format=JSON', 'tsv' => 'https://demo...&format=Tsv']
     * @throws \Throwable
     */
    protected function getApplicableDemoExampleUrls(string $pluginName, string $methodName, array $paramsData): array
    {
        // Get the example URLs for the success responses
        $parametersToSet = [
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today',
        ];

        $parametersToReplace = [];
        if (!empty($paramsData['custom'])) {
            foreach ($paramsData['custom'] as $customParam) {
                $paramName = strval($customParam['name']);
                if (isset($customParam['example']) && $customParam['example'] !== '') {
                    $example = $customParam['example'];

                    $decodedExample = [];
                    // If the type is array, try decoding it
                    if (in_array('array', array_keys($customParam['types']))) {
                        $decodedExample = json_decode($example, true);
                    }

                    // Check if the example is an array and needs special handling.
                    $queryString = !empty($decodedExample) ? Http::buildQuery([$paramName => $decodedExample]) : '';
                    if (stripos($queryString, urlencode($customParam['name'] . '[')) === 0) {
                        // Mark the param to be replaced and change the value to a placeholder
                        $parametersToReplace[$paramName] = $queryString;
                        $example = 'PlaceholderValue';
                    }

                    // Add the URL encoded param and value to the collection
                    $parametersToSet[$paramName] = urlencode($example);
                }
            }
        }
        $className = Request::getClassNameAPI($pluginName);
        $exampleUrl = $this->generator->getExampleUrl($className, $methodName, $parametersToSet);

        // Replace the placeholders with the actual array params now that we have an example URL
        if (!empty($exampleUrl) && !empty($parametersToReplace)) {
            foreach ($parametersToReplace as $name => $encodedValue) {
                $exampleUrl = str_replace('&' . $name . '=PlaceholderValue', '&' . $encodedValue, $exampleUrl);
            }
        }

        if (empty($exampleUrl)) {
            // If we couldn't get an example URL from the generator, try getting one from metadata
            $exampleUrl = $this->getReportExampleUrlFromMetadata($pluginName, $methodName);

            if (empty($exampleUrl)) {
                return [];
            }
        }

        $exampleUrl = 'https://demo.matomo.cloud/' . $exampleUrl;
        return [
            'xml' => $exampleUrl . '&format=xml&token_auth=anonymous',
            'json' => $exampleUrl . '&format=JSON&token_auth=anonymous',
            'tsv' => $exampleUrl . '&format=Tsv&token_auth=anonymous',
        ];
    }

    /**
     * Query demo.matomo.cloud for report metadata which can later be used to help determine good example URLs for
     * specific API endpoints. This method is only used when the example URL can't be determined using the default
     * method. This only works for endpoints associated with reports and have metadata provided by the containing
     * plugin. The response is cached as a property so the request is only made once regardless of how many times this
     * method is called. The exception is if a valid response wasn't received. In that case, it will keep making the
     * request until a non-empty response is received.
     *
     * @return array|array[] The decoded JSON array of all the report metadata from the demo server.
     * @throws \Exception
     */
    protected function getDemoReportMetadata(): array
    {
        if (is_array($this->reportMetadata) && count($this->reportMetadata)) {
            return $this->reportMetadata;
        }

        $url = 'https://demo.matomo.cloud/index.php?module=API&method=API.getReportMetadata&format=JSON&idSite=1&hideMetricsDoc=0&showSubtableReports=0&filter_limit=-1&period=day';
        try {
            $response = Http::sendHttpRequestBy(
                Http::getTransportMethod(),
                $url,
                $timeout = 30, // We can use a somewhat longer timeout for this request since it's cached afterward.
                $userAgent = null,
                $destinationPath = null,
                $file = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $acceptInvalidSslCertificate = true,
                $byteRange = false,
                $getExtendedInfo = true,
                $httpMethod = 'GET'
            );
        } catch (\Exception $e) {
            // Add a little bit more context for troubleshooting the failed request
            throw new \Exception('Error getting report metadata from URL: ' . $url . PHP_EOL . $e, 0, $e);
        }

        if (empty($response['data']) || ($response['status'] ?? 1) !== 200 || strpos($response['data'], 'Error: ') === 0) {
            return [];
        }

        $this->reportMetadata = json_decode($response['data'], true) ?? [];

        return $this->reportMetadata;
    }

    /**
     * Take the example URL and query the endpoint for an example response, hiding subtables. If a response isn't
     * received from demo.matomo.cloud, it can try using a temporary token to make the request against the current
     * instance of Matomo.
     *
     * @param string $url The full example URL. E.g.
     * https://demo.matomo.cloud/?module=API&method=CustomReports.getConfiguredReports&idSite=1&format=xml&token_auth=anonymous
     * @param bool $useLocalToken A boolean indicating whether to get a temporary token and try the request against the
     * currently running Matomo instance.
     *
     * @return string The response received from the API endpoint if no error was received or the response wasn't empty.
     * An empty string is returned by default.
     * @throws \Throwable
     */
    protected function getExampleIfAvailable(string $url, bool $useLocalToken = false): string
    {
        // If the flag to use a temp token is set, get a token and update the request URL
        $tempUrl = $url . '&hideIdSubDatable=1';
        if ($useLocalToken) {
            $token = Piwik::requestTemporarySystemAuthToken('OpenApiDocs', 24);
            $tempUrl = str_replace('&token_auth=anonymous', '&token_auth=' . $token, $tempUrl);
            $tempUrl = str_replace('https://demo.matomo.cloud/', SettingsPiwik::getPiwikUrl(), $tempUrl);
        }
        try {
            $response = Http::sendHttpRequestBy(
                Http::getTransportMethod(),
                $tempUrl,
                $timeout = 10,
                $userAgent = null,
                $destinationPath = null,
                $file = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $acceptInvalidSslCertificate = true,
                $byteRange = false,
                $getExtendedInfo = true,
                $httpMethod = 'GET'
            );
        } catch (\Throwable $e) {
            // Add a little bit more context for troubleshooting the failed request
            throw new \Exception('Error getting example from URL: ' . $url . PHP_EOL . $e, 0, $e);
        }

        // If the example didn't load or resulted in an error, simply return an empty string
        if (
            empty($response['data']) || ($response['status'] ?? 1) !== 200
            || strpos($response['data'], 'Error: ') === 0
            || stripos(str_replace(["\n", "\t"], '', $response['data']), '<result><error message=') !== false
            || stripos($response['data'], '"result":"error"') !== false
            || stripos($response['data'], '<result />') !== false
            || trim($response['data']) === '[]'
        ) {
            return '';
        }
        $body = $response['data'];

        // Convert the XML responses into a JSON object and then encode it into a string. This is helpful for building schemas.
        if (stripos($url, 'format=xml') !== false) {
            $body = json_encode($this->convertExampleXmlToObject($body));
        }

        return $body;
    }

    /**
     * Try to build an example URL for a specific API method using report metadata. This queries the demo server for
     * report metadata to get examples of existing reports which can be used as example URLS. If no metadata matches the
     * provided plugin and method, an empty string is returned. Likewise, when no valid response is received for the
     * URL. NOTE: This should only be used if the old DocumentationGenerator didn't provide example URLs.
     *
     * @param string $pluginName The name of the plugin. E.g. TagManager.
     * @param string $methodName The name of the plugin specific API method. E.g. getCustomReport.
     *
     * @return string The example URL with only the required query parameters and only if a valid example response was
     * received when the URL was queried. Empty string if no URL could be determined or no valid response was received.
     * @throws \Throwable
     */
    protected function getReportExampleUrlFromMetadata(string $pluginName, string $methodName): string
    {
        $metadataArray = $this->getDemoReportMetadata();
        if (empty($metadataArray)) {
            return '';
        }

        foreach ($metadataArray as $metadata) {
            if (empty($metadata['module']) || empty($metadata['action'])) {
                continue;
            }

            // Keep trying until we find a good match
            if ($metadata['module'] === $pluginName && $metadata['action'] === $methodName) {
                if (empty($metadata['imageGraphUrl'])) {
                    continue;
                }

                $url = str_replace(
                    [
                        'ImageGraph.get',
                        "&apiModule={$pluginName}&apiAction={$methodName}",
                    ],
                    [
                        $pluginName . '.' . $methodName,
                        '',
                    ],
                    $metadata['imageGraphUrl']
                );

                // If we get a valid response, return the URL
                if (!empty($this->getExampleIfAvailable('https://demo.matomo.cloud/' . $url))) {
                    return $url;
                }
            }
        }

        return '';
    }

    /**
     * Take an XML string, deserialise it, and convert it into a JSON object structured correctly for an XML schema
     * example. E.g. <result><row>Value1</row><row>Value2</row></result> to ["row" => ["Value1","Value2"]] or
     * <result><row><child>Value1</child></row><row><child>Value2</child></row></result> to
     * ["row" => [{"child":"Value1"},{"child":"Value2"}]]
     *
     * @param string $xml The XML string of an example response for an API endpoint.
     *
     * @return array The array representation of the JSON object example structured correctly for an XML schema. E.g.
     * ["row" => [{"child":"Value1"},{"child":"Value2"}]]
     * @throws \Exception
     */
    public function convertExampleXmlToObject(string $xml): array
    {
        $root = new \SimpleXMLElement($xml);

        $toArray = function (\SimpleXMLElement $node) use (&$toArray) {
            if (!count($node->children())) {
                return trim((string)$node);
            }
            // Group children by tag name; repeated names become arrays
            $grouped = [];
            foreach ($node->children() as $child) {
                $name = $child->getName();
                $grouped[$name][] = $toArray($child);
            }
            return array_map(function ($items) {
                return (count($items) === 1) ? $items[0] : $items;
            }, $grouped);
        };

        $result = $toArray($root);
        if (!is_array($result)) {
            return [$result];
        }

        return $result;
    }

    /**
     * Build the array of potential responses for the API method. E.g. a response for 200, 400, 401, etc.
     *
     * @param array $rules An array of configs determining which responses to include by default.
     * @param string $plugin Name of the plugin. E.g. TagManager.
     * @param string $method The name of the method being annotated.
     * @param \ReflectionMethod $reflectionMethod The reflective representation of the method to provide metadata.
     * @param array $paramsData An array of already built method parameter data. This is used while building example
     * URLs because the generator doesn't know what value to use for non-global parameters like idSite and period. We
     * check the paramsData to see if an example value was provided for all the required parameters so that an example
     * can be queried.
     *
     * @return array A collection of annotation lines for each of the expected potential responses for the method.
     * @throws \Throwable
     */
    protected function determineResponses(array $rules, string $plugin, string $method, \ReflectionMethod $reflectionMethod, array $paramsData): array
    {
        $responses = [];

        // Try to determine the success response using the return type and/or doc-block return type
        $returnType = $reflectionMethod->getReturnType();
        $responseInfo = $this->getResponseInfoFromDocBlock($reflectionMethod->getDocComment());
        if (!empty($returnType) && $returnType->isBuiltin()) {
            $responseInfo['type'] = $this->getOpenApiTypeFromPhpType(strval($returnType));
        }

        $successRef = null;
        $successArray = ['code' => 200, 'description' => ''];
        if (isset($rules['plugins'][$plugin]['successResponseByMethod'][$method])) {
            $successRef = $rules['plugins'][$plugin]['successResponseByMethod'][$method];
        }
        // TODO - See if there's a way to auto-handle custom objects, especially common stuff like DataTable\DataTableInterface
        if ($successRef) {
            $successArray['ref'] = $successRef;
        }

        // If the return type is void, use the generic response type
        if (empty($successArray['ref']) && !empty($returnType) && strval($returnType) === 'void') {
            $successArray['ref'] = '#/components/responses/GenericSuccessNoBody';
        }

        // If it's a generic type and there's no custom description, use one of the global generic responses
        if (empty($successArray['ref']) && !empty($responseInfo['type']) && empty($responseInfo['description'])) {
            $ref = '';
            switch ($responseInfo['type']) {
                case 'array':
                    $ref = '#/components/responses/GenericArray';
                    break;
                case 'integer':
                    $ref = '#/components/responses/GenericInteger';
                    break;
                case 'boolean':
                    $ref = '#/components/responses/GenericBoolean';
                    break;
                case 'string':
                    $ref = '#/components/responses/GenericString';
                    break;
            }

            if (!empty($ref)) {
                $successArray['ref'] = $ref;
            }
        }

        if (!empty($responseInfo['description'])) {
            $successArray['description'] = $responseInfo['description'];
        }

        $responseSchema = !empty($responseInfo['type']) ? $this->buildSchemaObjectArray($responseInfo['type']) : [];

        $mediaTypes = [];
        // This simply reuses the example URLs used by the current documentation, but some endpoints don't work because authentication is required
        // TODO - Come up with a way to demo examples for endpoints which require authentication. E.g. hit a live endpoint server-side and replace any potentially sensitive data...
        $exampleUrls = $this->getApplicableDemoExampleUrls($plugin, $method, $paramsData);
        foreach ($exampleUrls as $type => $url) {
            $contentType = $type === 'json' ? 'application/json' : ($type === 'xml' ? 'text/xml' : 'application/vnd.ms-excel');
            if ($type === 'tsv') {
                $url .= '&convertToUnicode=0';
            }
            $exampleValue = $this->getExampleIfAvailable($url);
            // If the example lookup failed, try making the same request locally
            $isLocalExample = false;
            if (empty($exampleValue)) {
                $exampleValue = $this->getExampleIfAvailable($url, true);
                $isLocalExample = true;
            }
            if (strlen($exampleValue) > self::EXAMPLE_CHAR_LIMIT) {
                $exampleValue = $this->cutExampleCloseToCharLimit($exampleValue, $type);
            }
            $jsonSchema = $type === 'json' ? $this->buildSchemaAnnotationFromJsonExample(json_decode($exampleValue, true) ?? []) : [];
            $xmlSchema = $type === 'xml' ? $this->buildSchemaAnnotationFromXmlExample(json_decode($exampleValue, true) ?? []) : [];
            // Make sure that the local example doesn't have anything bad in it
            if ($isLocalExample) {
                // TODO - Obfuscate any potentially sensitive data
            }

            if (in_array($type, ['json', 'xml'])) {
                // The annotation expects objects and not arrays, so replace [] with {}
                $exampleValue = str_replace(['[', ']'], ['{', '}'], $exampleValue);
                // Escape quotes differently for the annotation examples
                $exampleValue = str_replace('\"', '""', $exampleValue);
            }

            // Skip if there was no example response
            if (empty($exampleValue)) {
                continue;
            }

            $mediaType = [
                'mediaType="' . $contentType . '"',
            ];
            if ($type !== 'tsv') {
                $mediaType[] = 'example=' . $exampleValue;
            }
            // If a type was found, add it as a schema to the media type
            if ($type === 'json') {
                $responseSchema = !empty($jsonSchema) ? $jsonSchema : ($responseSchema ?: []);
                $mediaType = array_merge($mediaType, $responseSchema);
            }
            if ($type === 'tsv') {
                // Escape quotes differently for the annotation examples
                $exampleValue = str_replace('"', '""', $exampleValue);
                $mediaType[] = 'example="' . $exampleValue . '"';
            }
            if ($type === 'xml') {
                $mediaType = array_merge($mediaType, $xmlSchema);
            }
            $mediaTypes[] = $mediaType;
        }
        if (!empty($mediaTypes)) {
            $successArray['mediaTypes'] = $mediaTypes;

            // If there are media types we shouldn't need the unknown type description
            if (!empty($successArray['description']) && $successArray['description'] === 'Response of unknown type') {
                $successArray['description'] = '';
            }
        } else {
            // Make sure the schema is included in there are no examples
            $successArray['schema'] = $responseSchema;
        }

        $tsvExampleLink = 'TSV (N/A)';
        if (count($mediaTypes) > 2) {
            $tsvExampleLink = "[TSV (Excel)]({$exampleUrls['tsv']})";
        }
        $descriptionLinks = empty($exampleUrls) ? '' : "[XML]({$exampleUrls['xml']}), [JSON]({$exampleUrls['json']}), $tsvExampleLink";
        $descriptionLinks = !empty($descriptionLinks) ? 'Example links: ' . $descriptionLinks : $descriptionLinks;

        // Append the links to the description with a prefix linebreak. If there's no description, skip the break
        $successArray['description'] .= (!empty($successArray['description']) && !empty($descriptionLinks) ? '</br>' : '') . $descriptionLinks;

        $responses[] = $successArray;

        if (!empty($rules['defaultErrorResponseRefs'])) {
            foreach ($rules['defaultErrorResponseRefs'] as $errorRef) {
                $responses[] = $errorRef;
            }
        }

        return $responses;
    }

    /**
     * Take a string example and make sure that it is close to the max char limit. There's a little wiggle room due to
     * wrapping elements and whitespace characters, but it should be within 100 characters of the limit. To do this, we
     * deserialise the example based on type and iterate over the first-level properties and append them to a new
     * example string. If a single property exceeds the limit or will if added to the newly built string, we skip it. If
     * none of the base properties are small enough, we simply return an empty string.
     *
     * @param string $exampleValue The example response received from the demo or other server.
     * @param string $type The type of the parameter. E.g. string, integer, number, boolean, array, ...
     *
     * @return string A new example string within a reasonable variation from the limit. If no row of the example fits
     * within the limit, the result is an empty string.
     */
    public function cutExampleCloseToCharLimit(string $exampleValue, string $type): string
    {
        if (empty($exampleValue)) {
            return '';
        }

        // Special handling for TSV
        if ($type === 'tsv') {
            $finalExample = '';
            foreach (explode("\n", $exampleValue) as $row) {
                // Don't add the row if it would exceed the limit
                if (
                    strlen($row) > self::EXAMPLE_CHAR_LIMIT
                    || strlen($finalExample . $row) > self::EXAMPLE_CHAR_LIMIT
                ) {
                    continue;
                }

                $finalExample .= $row . "\n";
            }

            return rtrim($finalExample);
        }

        $decodedRows = $rows = json_decode($exampleValue, true);
        if (!is_array($decodedRows) || count($decodedRows) === 0) {
            return '';
        }

        if (!empty($decodedRows['row']) && is_array($decodedRows['row'])) {
            $rows = $decodedRows['row'];
        }
        $newRows = [];
        foreach ($rows as $row) {
            // Don't add the row if it would exceed the limit
            if (
                strlen(json_encode($row)) > self::EXAMPLE_CHAR_LIMIT
                || strlen(json_encode(array_merge($newRows, [$row]))) > self::EXAMPLE_CHAR_LIMIT
            ) {
                continue;
            }

            $newRows[] = $row;
        }

        if (empty($newRows)) {
            return '';
        }

        if (!empty($decodedRows['row'])) {
            $decodedRows['row'] = $newRows;
        } else {
            $decodedRows = $newRows;
        }

        return json_encode($decodedRows);
    }

    /**
     * Take the deserialised structure of an JSON object and build the lines of an OA\Schema annotation object for it.
     *
     * @param array $jsonArrayObject Nested array of properties of the JSON object.
     *
     * @return array Collection of potentially nested arrays representing an OA\Property annotation object.
     */
    public function buildSchemaAnnotationFromJsonExample(array $jsonArrayObject): array
    {
        // Since the schema is pretty much the same as the property, let's just build a property and replace the key
        $propertyLines = $this->buildPropertyAnnotationFromJsonExample('', $jsonArrayObject);

        return ['@OA\Schema' => $propertyLines['@OA\Property']];
    }

    /**
     * Take the deserialised structure of an JSON object and build the lines of an OA\Property annotation object for it.
     *
     * @param string $propName Name of the JSON property.
     * @param array $values Nested array of properties of the JSON property.
     *
     * @return array Collection of potentially nested arrays representing an OA\Property annotation object.
     */
    public function buildPropertyAnnotationFromJsonExample(string $propName, array $values): array
    {
        $type = 'object';
        // If the first key isn't a string, it's an array
        $keys = array_keys($values);
        if (!is_string(reset($keys))) {
            $type = 'array';
        }

        // Set the common properties
        $propertyLines = !empty($propName) ? [sprintf('property="%s",', $propName)] : [];
        $propertyLines[] = sprintf('type="%s",', $type);

        // If it's an array, we only care about the structure of the first element since they should be the same
        if ($type === 'array') {
            // Just show as generic items if it's not an object (array)
            if (!is_array($values[0] ?? null)) {
                return ['@OA\Property' => array_merge($propertyLines, ['@OA\Items()'])];
            }

            // Build the lines of descendents recursively
            $childLines = $this->buildPropertyAnnotationFromJsonExample('', $values[0]);
            return [
                '@OA\Property' => array_merge($propertyLines, ['@OA\Items' => array_merge([
                    'type="object",',
                    'additionalProperties=true,',
                ], $childLines)]),
            ];
        }

        $childLines = [];
        // Since this is an object, build properties for each child, recursively if any children are arrays/objects
        foreach ($values as $key => $value) {
            // If it's not an array, add a simple property string and skip to the next child
            if (!is_array($value)) {
                $typesString = '{"string", "number", "integer", "boolean", "array", "object", "null"}';
                if (is_string($value)) {
                    $typesString = '"string"';
                } elseif (is_int($value)) {
                    $typesString = '"integer"';
                } elseif (is_bool($value)) {
                    $typesString = '"boolean"';
                }
                $childLines[] = sprintf('@OA\Property(property="%s", type=%s)', $key, $typesString);
                continue;
            }

            $childLines = array_merge($childLines, $this->buildPropertyAnnotationFromJsonExample($key, $value));
        }

        return ['@OA\Property' => array_merge($propertyLines, $childLines)];
    }

    /**
     * Take the deserialised structure of an XML node and build the lines of an OA\Schema annotation object for it.
     *
     * @param array $xmlArrayObject Nested array of properties of the XML node.
     * @param string $root Name of the root element. The default is 'result'.
     *
     * @return array Collection of potentially nested arrays representing an OA\Property annotation object.
     */
    public function buildSchemaAnnotationFromXmlExample(array $xmlArrayObject, string $root = 'result'): array
    {
        $lines = [
            'type="object",',
            sprintf('@OA\Xml(name="%s"),', $root),
        ];

        foreach ($xmlArrayObject as $key => $value) {
            // If the value is not an array, skip
            if (!is_array($value)) {
                continue;
            }

            if (count($value) === 1) {
                $keys = array_keys($value);
                // Skip if it's not a named property
                if (!is_string(reset($keys)) && !is_array(reset($value))) {
                    continue;
                }
            }

            $lines[] = $this->buildPropertyAnnotationFromXmlExample($key, $value);
        }

        return ['@OA\Schema' => $lines];
    }

    /**
     * Take the deserialised structure of an XML node and build the lines of an OA\Property annotation object for it.
     *
     * @param string $propName Name of the XML node.
     * @param array $values Nested array of properties of the XML node.
     *
     * @return array Collection of potentially nested arrays representing an OA\Property annotation object.
     */
    public function buildPropertyAnnotationFromXmlExample(string $propName, array $values): array
    {
        $type = 'object';
        if ($propName === 'row') {
            $type = 'array';
            $values = is_array($values[0] ?? null) ? $values[0] : [];
        }

        // Set the common properties
        $propertyLines = [
            sprintf('property="%s",', $propName),
            sprintf('type="%s",', $type),
        ];

        $childLines = [];
        // Recursively check if any of the children are arrays
        foreach ($values as $key => $value) {
            // If it's not an array, skip
            if (!is_array($value)) {
                continue;
            }

            // Handle nested arrays
            if (!is_string($key)) {
                if (!is_array(reset($value))) {
                    continue;
                }

                $keys = array_keys($value);
                $key = reset($keys);
                $value = $value[$key];
            }

            $childLines[] = $this->buildPropertyAnnotationFromXmlExample($key, $value);
        }

        // If the object is for row, merge any children with the items object
        if ($propName === 'row') {
            $itemProperties = [
                'type="object",',
                '@OA\Xml(name="row"),',
                'additionalProperties=true,',
            ];

            // Handle arrays of strings which don't have named properties
            $keys = array_keys($values);
            if (!is_string(reset($keys))) {
                $itemProperties = ['type="string"'];
            }

            $childLines = ['@OA\Items' => array_merge($itemProperties, $childLines)];
        }

        return ['@OA\Property' => array_merge($propertyLines, $childLines)];
    }

    /**
     * Take a list of lines and remove the trailing comma from the last line.
     *
     * @param string[] $lines List of lines for an annotation passed by reference.
     *
     * @return void
     */
    public function removeTrailingCommaFromLastLine(array &$lines): void
    {
        if (!empty($lines)) {
            $last = array_pop($lines);
            $lines[] = rtrim($last, ',');
        }
    }

    /**
     * Generic method for building the array of lines for an annotation object. It handles adding the indent based on
     * the level. For example, if it's nested under 3 other objects, the indent will be 12 spaces (3 x 4-space tabs).
     *
     * @param string $objectName The type of object. E.g. OA\Schema or OA\Property
     * @param array $objectProperties A nested array of the properties of the object. E.g. type, example, OA\Schema, ...
     * @param int $indent The count of indents/tabs based on the nesting the object. E.g. 0 = none & 2 = indented twice.
     *
     * @return array The lines of the annotation object with correct opening/closing characters (usually parenthesis),
     * and proper indentation for each line.
     */
    public function buildLinesForAnnotationObject(string $objectName, array $objectProperties, int $indent = 0): array
    {
        $indentString = str_repeat('    ', $indent);
        $innerIndentString = str_repeat('    ', $indent + 1);
        $lines = [];
        foreach ($objectProperties as $name => $property) {
            if (is_string($property)) {
                $lines[] = $innerIndentString . $property . (substr($property, -1) !== ',' ? ',' : '');
                continue;
            }

            if (is_string($name)) {
                $lines = array_merge($lines, $this->buildLinesForAnnotationObject($name, $property, $indent + 1));
                continue;
            }

            // If it's not an object, then it's an array of similarly named objects, like parameters
            foreach ($property as $subPropIndex => $subProperty) {
                $lines = array_merge($lines, $this->buildLinesForAnnotationObject($subPropIndex, $subProperty, $indent + 1));
            }
        }

        $this->removeTrailingCommaFromLastLine($lines);

        // Default to parenthesis, but override when necessary
        $openingCharacter = '(';
        $closingCharacter = ')';
        if (substr($objectName, -2) === '={') {
            $openingCharacter = '';
            $closingCharacter = '}';
        }

        // Return the compiled lines wrapped with the opening and closing parenthesis/braces
        return array_merge([$indentString . $objectName . $openingCharacter], $lines, [$indentString . $closingCharacter . ',']);
    }

    /**
     * Build the array of lines for the OA\Schema annotation object for a single type.
     *
     * @param string $type The type of the parameter. E.g. string, integer, number, boolean, array, ...
     * @param string $subType This can specify the subtype for arrays. E.g. integer for int[] or string for string[].
     * @param string $default The optional default value for the type. Default is no value.
     * @param string $example The optional example value for the type. Default is empty string which indicated no value.
     *
     * @return array[]
     */
    public function buildSchemaObjectArray(string $type, string $subType = '', string $default = NoDefaultValue::class, string $example = ''): array
    {
        $schemaMap = ['type="' . $type . '"'];
        if (($example) !== '') {
            $schemaMap[] = 'example=' . $this->wrapStringWithQuotes($example, $type);
        }
        $subTypeString = '';
        if (!empty($subType)) {
            $subTypeString = 'type="' . $subType . '"';
        }
        if ($type === 'array') {
            $schemaMap[] = '@OA\Items(' . $subTypeString . ')';
            if ($default === '[]') {
                $default = '{}';
            }
        }

        if ($this->shouldIncludeDefault($type, $default)) {
            $schemaMap[] = 'default=' . $this->wrapStringWithQuotes($default, $type);
        }

        return ['@OA\Schema' => $schemaMap];
    }

    /**
     * Wrap an example of default value string with quotes. E.g. "exampleValue". Depending on the type and the value,
     * the quotes may be omitted.
     *
     * @param string $string Value for the example or default.
     * @param string $type The type of the parameter. E.g. string, integer, number, boolean, array, ...
     * @param string $quoteCharacter What to wrap the value with, if it should be wrapped. The default is double quote.
     *
     * @return string
     */
    public function wrapStringWithQuotes(string $string, string $type, string $quoteCharacter = '"'): string
    {
        if (in_array($type, ['integer', 'boolean', 'array'])) {
            return $string;
        }

        // If it's an object or empty string, there's no need to wrap with quotes
        if (in_array($string, ['{}', "''", '""'])) {
            $quoteCharacter = '';
        }

        return "{$quoteCharacter}{$string}{$quoteCharacter}";
    }

    /**
     * Indicates whether a specific parameter should include a default value in its annotation.
     *
     * @param string $type The type of the parameter. E.g. string, integer, number, boolean, array, ...
     * @param string $default The default value from reflection or doc block.
     *
     * @return bool Whether a default value should be included or not.
     */
    public function shouldIncludeDefault(string $type, string $default = NoDefaultValue::class): bool
    {
        if ($default === NoDefaultValue::class) {
            return false;
        }

        // Don't use true or false for default if it's not a boolean type
        if ($type !== 'boolean' && in_array(strtolower($default), ['false', 'true'])) {
            return false;
        }

        return true;
    }

    /**
     * Build the array for the OA\Schema annotation object for one or more types.
     *
     * @param array $typesMap The array of types where the keys are the types and the values are the subtypes, if any.
     * E.g. ['string' => null, 'array' => 'integer'] for idSites which can be an array or comma-separated-string of IDs.
     * The string key has a value of null because it has no subtype while the array has 'integer' because values should
     * be int IDs.
     * @param string $default The value to use as the default property of the schema. If it's an empty string, no
     * default is set.
     * @param string $example The value to use as the example property of the schema. If it's an empty string, no
     * example is set.
     *
     * @return array[] The collection of lines which make up the schema annotation object.
     */
    public function buildSchemaObjectArrays(array $typesMap, string $default = '', string $example = ''): array
    {
        $schemas = [];
        foreach ($typesMap as $type => $subType) {
            $schemas[] = $this->buildSchemaObjectArray($type, $subType ?? '', $default, $example);
        }

        if (count($schemas) === 1) {
            return $schemas[0];
        }

        return ['@OA\Schema' => ['oneOf={' => $schemas]];
    }

    /**
     * Build the full array of lines for an OA operation. E.g. OA\Get or OA\Post
     *
     * @param string $path The operation path. E.g. /index.php?module=API&method=CustomReports.getConfiguredReport
     * @param string $opId The string which uniquely identifies the operation across the entire OpenAPI spec. In order
     * to avoid potential duplicates, we use the plugin name and method name. E.g. CustomReports.getConfiguredReport
     * @param string $plugin The name of the plugin. E.g. CustomReports
     * @param array $params The compiled list of method parameters and key information about them, like type.
     * @param array $responses compiled list of method expected responses and key information about them, like type.
     * @param bool $isPost Indicates whether the operation is a POST. The default is false, meaning it's GET.
     *
     * @return string[] The array of all the lines of the operation annotation object.
     */
    public function compileOperationLines(string $path, string $opId, string $plugin, array $params, array $responses, bool $isPost = false): array
    {
        $operationValuesMap = [
            'path="' . $path . '"',
            'operationId="' . $opId . '"',
            'tags={"' . $plugin . '"}',
        ];
        foreach ($params['refs'] ?? [] as $ref) {
            $operationValuesMap[] = '@OA\Parameter(ref="' . $ref . '")';
        }
        foreach ($params['custom'] ?? [] as $param) {
            $paramMap = [
                'name="' . $param['name'] . '"',
                'in="query"',
                'required=' . $param['required'],
            ];
            if (!empty($param['description'])) {
                $paramMap[] = 'description="' . $param['description'] . '"';
            }
            $exampleString = $param['example'];
            if (in_array('array', array_keys($param['types']))) {
                // The annotation expects example objects and not arrays, so replace [] with {}
                $exampleString = str_replace(['[', ']'], ['{', '}'], $exampleString);
                // Escape quotes differently for the annotation examples
                $exampleString = str_replace('\"', '""', $exampleString);
            }
            $paramMap[] = $this->buildSchemaObjectArrays($param['types'], strval($param['default']), strval($exampleString));
            $operationValuesMap[] = ['@OA\Parameter' => $paramMap];
        }
        foreach ($responses as $response) {
            // Don't use the reference if there are media type examples
            if (isset($response['ref']) && empty($response['mediaTypes'])) {
                $code = $response['code'];
                $codeFormatted = is_numeric($code) ? (string)$code : '"' . $code . '"';
                $description = !empty($response['description']) && strpos($response['description'], 'Example links: [') !== false
                    ? ', description="' . $response['description'] . '"' : '';
                $operationValuesMap[] = '@OA\Response(response=' . $codeFormatted . $description . ', ref="' . $response['ref'] . '")';
            } else {
                $responsePropertyArray = [
                    'response=200',
                    'description="' . ($response['description'] ?? 'OK') . '"',
                ];
                if (!empty($response['schema'])) {
                    $responsePropertyArray = array_merge($responsePropertyArray, $response['schema']);
                }
                if (isset($response['mediaTypes']) && is_array($response['mediaTypes'])) {
                    foreach ($response['mediaTypes'] as $mediaType) {
                        $responsePropertyArray[] = ['@OA\MediaType' => $mediaType];
                    }
                }
                $operationValuesMap[] = ['@OA\Response' => $responsePropertyArray];
            }
        }

        $lines = $this->buildLinesForAnnotationObject('@OA\\' . ($isPost ? 'Post' : 'Get'), $operationValuesMap);

        // Trim the comma off the very last item at this level and return the array
        $this->removeTrailingCommaFromLastLine($lines);
        return $lines;
    }
}
