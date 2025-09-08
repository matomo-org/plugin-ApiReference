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
use Piwik\Plugin\Manager;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;

class AnnotationGenerator
{
    /**
     * @var DocumentationGenerator
     */
    protected $generator;

    public function __construct(DocumentationGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Use reflection to generate the OpenAPI annotations to be used by swagger-php.
     * - Tries to use virtual paths and x-runtime to keep paths unique and allow actual path generation
     * - Uses config.php to set default values.
     * - Uses config.php from plugin to override default configs.
     */
    public function generatePluginApiAnnotations(string $pluginName, bool $writeToFile = false)
    {
        BaseValidator::check('plugin', $pluginName, [ new NotEmpty() ]);
        Manager::getInstance()->checkIsPluginActivated($pluginName);

        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $rules = require $currentPluginDir . '/Annotations/config.php';
        $pluginDir = Manager::getInstance()::getPluginDirectory($pluginName);
        $pluginAnnotationDir = $pluginDir . '/OpenApi/Annotations';
        $pluginAnnotationPath = $pluginAnnotationDir . '/GeneratedAnnotations.php';
        // If the directory doesn't exist yet, create it
        if ($writeToFile && !is_dir($pluginAnnotationDir)) {
            mkdir($pluginAnnotationDir, 0777, true);
        }

        $className = Request::getClassNameAPI($pluginName);

        try {
            $reflectionClass = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            return false;
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

    protected function writeAnnotationsToFile(array $annotations, string $filePath, string $pluginName): void
    {
        $output = '';
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
        file_put_contents($filePath, implode(PHP_EOL, $lines));
    }

    protected function buildAnnotationForMethod(array $rules, string $pluginName, \ReflectionMethod $reflectionMethod): array
    {
        $existing = $reflectionMethod->getDocComment();
        // Skip methods which have been marked as internal or auto annotations disabled
        if (
            $existing !== false && (stripos($existing, 'OA-AUTO:OFF') !== false
                || stripos($existing, '@internal') !== false)
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
        $responses = $this->determineResponses($rules, $pluginName, $methodName);

        $isPost = !empty($rules['plugins'][$pluginName]['methodsRequiringPost'])
            && in_array($methodName, $rules['plugins'][$pluginName]['methodsRequiringPost']);

        return $this->compileOperationLines($path, $opId, $pluginName, $methodName, $params, $responses, $isPost);
    }

    protected function getParamInfoFromDocBlock(string $docBlock): array
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
                'type'     => (string) $param->type,
                'desc'     => $param->description,
                'byRef'    => $param->isReference,
                'variadic' => $param->isVariadic,
            ];
        }
        return $params;
    }

    protected function buildVirtualPath(string $virtualPathTemplate, string $plugin, string $method): string
    {
        return str_replace([ '{plugin}', '{method}' ], [ $plugin, $method ], $virtualPathTemplate);
    }

    protected function buildParameterAnnotation(string $paramName, array $paramMetadata, array $paramDocInfo): array
    {
        $docType = strtolower(trim($paramDocInfo['type'] ?? ''));
        $metaType = strtolower(trim($paramMetadata['type'] ?? $docType));
        $type = $metaType === 'string' && $docType !== 'string' ? $docType : $metaType;
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

        return [
            'name' => $paramName,
            'types' => $typesMap,
            'description' => $paramDocInfo['desc'] ?? '',
            'required' => $isRequired ? 'true' : 'false',
            'default' => !$isRequired ? json_encode($paramMetadata['default']) : '',
        ];
    }

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

            $customParams[] = $this->buildParameterAnnotation($name, $paramMetadata, $paramInfo);
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

    protected function getApplicableDemoExampleUrls(string $pluginName, string $methodName): array
    {
        // Get the example URLs for the success responses
        $parametersToSet = [
            'idSite' => 1,
            'period' => 'day',
            'date' => 'today'
        ];
        $className = Request::getClassNameAPI($pluginName);
        $exampleUrl = $this->generator->getExampleUrl($className, $methodName, $parametersToSet);
        if (empty($exampleUrl)) {
            return [];
        }

        $exampleUrl = 'https://demo.matomo.cloud/' . $exampleUrl;
        return [
            'xml' => $exampleUrl . '&filter_limit=2&format=xml&token_auth=anonymous',
            'json' => $exampleUrl . '&filter_limit=2&format=JSON&token_auth=anonymous',
            'tsv' => $exampleUrl . '&filter_limit=2&format=Tsv&token_auth=anonymous',
        ];
    }

    protected function getExampleIfAvailable(string $url): array
    {
        // Simply return the URL for anything other than JSON until we figure out how to better format those examples
        if (stripos($url, 'format=json') === false) {
            return ['externalValue' => $url];
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // If the example didn't load or is too big, simply include the URL instead of the string value
        if ($body === false || $status !== 200 || strlen($body) > 1000 || strpos($body, 'Error: ') === 0) {
            return ['externalValue' => $url];
        }

        // Clean up XML formatting a bit
        $body = trim($body);
        if (stripos($url, 'format=xml') !== false) {
            $body = str_replace(['<?xml version="1.0" encoding="utf-8" ?>', "\n", "\t", '"'], ['', '', '', '\"'], $body);
        }

        // The annotation expects an objects and not arrays
        if (stripos($url, 'format=json') !== false && stripos($body, '[') === 0) {
            $body = str_replace(['[', ']'], ['{', '}'], $body);
        }

        return ['value' => $body];
    }

    protected function determineResponses(array $rules, string $plugin, string $method): array
    {
        $responses = [];

        // TODO - Try to determine the success response using the return type and/or doc-block return type

        $successRef = null;
        $successArray = ['code' => 200];
        if (isset($rules['plugins'][$plugin]['successResponseByMethod'][$method])) {
            $successRef = $rules['plugins'][$plugin]['successResponseByMethod'][$method];
        }
        if ($successRef) {
            $successArray['ref'] = $successRef;
        }

        $mediaTypes = [];
        // This simply reuses the example URLs used by the current documentation, but some endpoints don't work because authentication is required
        // TODO - Come up with a way to demo examples for endpoints which require authentication. E.g. hit a live endpoint server-side and replace any potentially sensitive data...
        $exampleUrls = $this->getApplicableDemoExampleUrls($plugin, $method);
        foreach ($exampleUrls as $type => $url) {
            $contentType = $type === 'json' ? 'application/json' : ($type === 'xml' ? 'text/xml' : 'application/vnd.ms-excel');
            $exampleProperties = [
                'example="' . $type . 'DemoLink"',
                'summary="Example ' . $type . '"',
            ];
            $exampleValue = $this->getExampleIfAvailable($url);
            $valueKey = array_key_first($exampleValue);
            $value = '"' . array_pop($exampleValue) . '"';
            // Remove the surrounding quotes for JSON values
            if ($valueKey === 'value' && $type === 'json') {
                $value = substr($value, 1, -1);
            }
            $exampleProperties[] = $valueKey . '=' . $value;
            $mediaTypes[] = [
                'mediaType="' . $contentType . '"',
                '@OA\Examples' => $exampleProperties,
            ];
        }
        if (!empty($mediaTypes)) {
            $successArray['mediaTypes'] = $mediaTypes;
        }

        $responses[] = $successArray;

        if (!empty($rules['defaultErrorResponseRefs'])) {
            foreach ($rules['defaultErrorResponseRefs'] as $errorRef) {
                $responses[] = $errorRef;
            }
        }

        return $responses;
    }

    protected function removeTrailingCommaFromLastLine(&$lines): void
    {
        if (!empty($lines)) {
            $last = array_pop($lines);
            $lines[] = rtrim($last, ',');
        }
    }

    protected function buildLinesForAnnotationObject(string $objectName, array $objectProperties, int $indent = 0): array
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

    protected function buildSchemaObjectArray(string $type, string $subType = '', string $default = ''): array
    {
        $schemaMap = ['type="' . $type . '"'];
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

        if ($default !== '') {
            // TODO - Add some logic to only add default if it matches the type. E.g. false isn't a good default for string
            $schemaMap[] = 'default="' . $default . '"';
        }

        return ['@OA\Schema' => $schemaMap];
    }

    protected function buildSchemaObjectArrays(array $typesMap, string $default = ''): array
    {
        $schemas = [];
        foreach ($typesMap as $type => $subType) {
            $schemas[] = $this->buildSchemaObjectArray($type, $subType ?? '', $default);
        }

        if (count($schemas) === 1) {
            return $schemas[0];
        }

        return ['@OA\Schema' => ['oneOf={' => $schemas]];
    }

    protected function compileOperationLines(string $path, string $opId, string $plugin, string $method, array $params, array $responses, bool $isPost = false): array
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
            $paramMap[] = $this->buildSchemaObjectArrays($param['types'], strval($param['default']));
            $operationValuesMap[] = ['@OA\Parameter' => $paramMap];
        }
        foreach ($responses as $response) {
            if (isset($response['ref'])) {
                $code = $response['code'];
                $codeFormatted = is_numeric($code) ? (string)$code : '"' . $code . '"';
                $operationValuesMap[] = '@OA\Response(response=' . $codeFormatted . ', ref="' . $response['ref'] . '")';
            } else {
                $responsePropertyArray = [
                    'response=200',
                    'description="' . ($response['desc'] ?? 'OK') . '"',
                ];
                if (isset($response['mediaTypes']) && is_array($response['mediaTypes'])) {
                    foreach ($response['mediaTypes'] as $mediaType) {
                        $responsePropertyArray[] = ['@OA\MediaType' => $mediaType];
                    }
                }
                $operationValuesMap[] = ['@OA\Response' => $responsePropertyArray];
            }
        }
        $operationValuesMap[] = 'x={"runtime"={"entry":"index.php","query":{"module":"API","method":"' . $plugin . '.' . $method . '"}}}';

        $lines = $this->buildLinesForAnnotationObject('@OA\\' . ($isPost ? 'Post' : 'Get'), $operationValuesMap);

        // Trim the comma off the very last item at this level and return the array
        $this->removeTrailingCommaFromLastLine($lines);
        return $lines;
    }
}
