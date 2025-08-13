<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Annotations;

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
use function Symfony\Component\String\s;

class AnnotationGenerator
{
    /**
     * Use reflection to generate the OpenAPI annotations to be used by php-swagger.
     * - Tries to use virtual paths and x-runtime to keep paths unique and allow actual path generation
     * - Uses config.php to set default values.
     * - Uses config.php from plugin to override default configs.
     */
    public function generatePluginApiAnnotations(string $pluginName)
    {
        BaseValidator::check('plugin', $pluginName, [ new NotEmpty() ]);
        Manager::getInstance()->checkIsPluginActivated($pluginName);

        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $rules = require $currentPluginDir . '/Annotations/config.php';
        $pluginDir = Manager::getInstance()::getPluginDirectory($pluginName);
        $pluginRules = require $pluginDir . '/OpenApi/Annotations/config.php';
        $rules['plugins'] = [ $pluginName => $pluginRules ];

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

            $reflectionMethod = $reflectionClass->getMethod($metadataMethod);
            $existing = $reflectionMethod->getDocComment();
            // Skip methods which have been marked as internal or auto annotations disabled
            if ($existing !== false && (stripos($existing, 'OA-AUTO:OFF') !== false
                || stripos($existing, '@internal') !== false)) {
                continue;
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

            $annotations[] = $this->compileOperationLines($path, $opId, $pluginName, $methodName, $params, $responses, $isPost);
        }

        if (empty($annotations)) {
            return false;
        }

        return $annotations;
    }

    function getParamInfoFromDocBlock(string $docBlock): array {
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

    function buildVirtualPath(string $virtualPathTemplate, string $plugin, string $method): string
    {
        return str_replace([ '{plugin}', '{method}' ], [ $plugin, $method ], $virtualPathTemplate);
    }

    function determineParameters(array $rules, string $plugin, string $method, \ReflectionMethod $reflectionMethod): array
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

            $type = $paramMetadata['type'] ?? $paramInfo['type'] ?? '';
            // TODO - Properly map the internal types to OpenAPI types
            switch (strtolower($type)) {
                case 'array':
                    $type = 'array';
                    break;
                case 'int':
                    $type = 'integer';
                    break;
                case 'bool':
                case 'boolean':
                    $type = 'boolean';
                    break;
                default:
                    $type = 'string';
            }

            $customParams[] = [
                'name' => $name,
                'type' => $type,
                'description' => $paramInfo['desc'] ?? '',
                'required' => empty($paramMetadata['allowsNull']) ? 'true' : 'false',
            ];
        }

        return [
            'refs' => array_values(array_unique($refs)),
            'custom' => $customParams,
        ];
    }

    function determineResponses(array $rules, string $plugin, string $method): array
    {
        $responses = [];

        $successRef = null;
        if (isset($rules['plugins'][$plugin]['successResponseByMethod'][$method])) {
            $successRef = $rules['plugins'][$plugin]['successResponseByMethod'][$method];
        }
        if ($successRef) {
            $responses[] = [ 'code' => 200, 'ref' => $successRef ];
        } else {
            $responses[] = [ 'code' => 200 ];
        }

        if (!empty($rules['defaultErrorResponseRefs'])) {
            foreach ($rules['defaultErrorResponseRefs'] as $errorRef) {
                $responses[] = $errorRef;
            }
        }

        return $responses;
    }

    function compileOperationLines(string $path, string $opId, string $plugin, string $method, array $params, array $responses, bool $isPost = false): array
    {
        $httpMethod = $isPost ? 'Post' : 'Get';
        $lines = [];
        $lines[] = '@OA\\' . $httpMethod . '(';
        $lines[] = '    path="' . $path . '",';
        $lines[] = '    operationId="' . $opId . '",';
        $lines[] = '    tags={"' . $plugin . '"},';

        foreach ($params['refs'] ?? [] as $ref) {
            $lines[] = '    @OA\Parameter(ref="' . $ref . '"),';
        }

        foreach ($params['custom'] ?? [] as $param) {
            // TODO - Finish implementing this
            $lines[] = '    @OA\Parameter(),';
            $lines[] = '        name="' . $param['name'] . '",';
            $lines[] = '        in="query",';
            $lines[] = '        required="' . $param['required'] . '",';
            $lines[] = '        @OA\Schema(';
            $lines[] = '            type="' . $param['type'] . '",';
            $lines[] = '        ),';
            $lines[] = '    ),';
        }

        foreach ($responses as $response) {
            if (isset($response['ref'])) {
                $code = $response['code'];
                $codeFormatted = is_numeric($code) ? (string)$code : '"' . $code . '"';
                $lines[] = '    @OA\Response(response=' . $codeFormatted . ', ref="' . $response['ref'] . '"),';
            } else {
                $desc = $response['desc'] ?? 'OK';
                $lines[] = '    @OA\Response(response=200, description="' . addcslashes($desc, '"') . '"),';
            }
        }

        $lines[] = '    x={"runtime"={"entry":"index.php","query":{"module":"API","method":"' . $plugin . '.' . $method . '"}}}';
        $lines[] = ')';
        
        return $lines;
    }
}
