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

            $paramRefs = $this->determineParameterReferences($rules, $pluginName, $methodName, $reflectionMethod);
            $responses = $this->determineResponses($rules, $pluginName, $methodName);

            $isPost = !empty($rules['plugins'][$pluginName]['methodsRequiringPost'])
                && in_array($methodName, $rules['plugins'][$pluginName]['methodsRequiringPost']);

            $annotations[] = $this->compileOperationLines($path, $opId, $pluginName, $methodName, $paramRefs, $responses, $isPost);
        }

        if (empty($annotations)) {
            return false;
        }

        return $annotations;
    }

    function buildVirtualPath(string $virtualPathTemplate, string $plugin, string $method): string
    {
        return str_replace([ '{plugin}', '{method}' ], [ $plugin, $method ], $virtualPathTemplate);
    }

    function determineParameterReferences(array $rules, string $plugin, string $method, \ReflectionMethod $rm): array
    {
        $refs = [];

        if (!empty($rules['defaultParamRefs'])) {
            $refs = array_merge($refs, $rules['defaultParamRefs']);
        }

        if (isset($rules['plugins'][$plugin]['paramRefsByMethod'][$method])) {
            $refs = array_merge($refs, $rules['plugins'][$plugin]['paramRefsByMethod'][$method]);
        }

        return array_values(array_unique($refs));
    }

    function determineResponses(array $rules, string $plugin, string $method): array
    {
        $out = [];

        $successRef = null;
        if (isset($rules['plugins'][$plugin]['successResponseByMethod'][$method])) {
            $successRef = $rules['plugins'][$plugin]['successResponseByMethod'][$method];
        }
        if ($successRef) {
            $out[] = [ 'code' => 200, 'ref' => $successRef ];
        } else {
            $out[] = [ 'code' => 200, 'desc' => 'OK' ];
        }

        if (!empty($rules['defaultErrorResponseRefs'])) {
            foreach ($rules['defaultErrorResponseRefs'] as $err) {
                $out[] = $err; // ['code'=>..., 'ref'=>...]
            }
        }

        return $out;
    }

    function compileOperationLines(string $path, string $opId, string $plugin, string $method, array $paramRefs, array $responses, bool $isPost = false): array
    {
        $httpMethod = $isPost ? 'Post' : 'Get';
        $lines = [];
        $lines[] = '@OA\\' . $httpMethod . '(';
        $lines[] = '    path="' . $path . '",';
        $lines[] = '    operationId="' . $opId . '",';
        $lines[] = '    tags={"' . $plugin . '"},';

        foreach ($paramRefs as $ref) {
            $lines[] = '    @OA\Parameter(ref="' . $ref . '"),';
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
