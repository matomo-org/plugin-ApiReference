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
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

class ApiMethodInfoExtractor
{
    /**
     * Look up the Matomo Reporting API methods for the specified plugin(s) and output the basic information for each.
     * This includes the comment block, parameter information, and things like that. This can then be fed to a secure
     * AI model which can come up with suggestions on how to improve the descriptions, type hinting, etc.
     *
     * @param string $pluginName Name or comma-separated list of names of the plugins to extract API endpoint info for.
     * @param bool $writeToFile Whether to output the result to console or write it to tmp file.
     *
     * @return string String result of the extracted info.
     * @throws \Exception
     */
    public function extractMethodInfo(string $pluginName, bool $writeToFile = false): string
    {
        BaseValidator::check('plugin', $pluginName, [new NotEmpty()]);
        $pluginNames = explode(',', $pluginName);

        BaseValidator::check('pluginNames', $pluginNames, [new NotEmpty()]);
        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');

        $methodInfoArray = [];
        foreach ($pluginNames as $plugin) {
            BaseValidator::check('pluginName', $plugin, [new NotEmpty()]);
            Manager::getInstance()->checkIsPluginActivated($plugin);

            $className = Request::getClassNameAPI($plugin);

            $result = $this->buildEndpointInfoArrayForApiClass($className);
            if (!empty($result)) {
                $methodInfoArray = array_merge($methodInfoArray, $result);
            }
        }
        $methodInfoString = json_encode($methodInfoArray);

        $fileBaseName = 'matomo';
        // If there's only one plugin, name the spec after the plugin
        if (count($pluginNames) === 1) {
            $fileBaseName = $pluginNames[0];
        }

        if ($writeToFile) {
            $pluginSpecPath = $currentPluginDir . OpenApiDocs::GENERATED_ANNOTATIONS_PATH . $fileBaseName . '_api_method_info.json';
            file_put_contents($pluginSpecPath, $methodInfoString);
        }

        return $methodInfoString;
    }

    /**
     * Build the array of info about all the public API endpoints for a specific API class. It uses reflection and the
     * metadata from the Proxy class used to build documentation.
     *
     * @param string $apiClassName Full name of the class which can be used to instantiate a ReflectionClass.
     *
     * @return array[] Collection of key details about each public API endpoint in the specified class.
     */
    public function buildEndpointInfoArrayForApiClass(string $apiClassName): array
    {
        try {
            $reflectionClass = new \ReflectionClass($apiClassName);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException('Unable to get reflection class for API class: ' . $apiClassName, 0, $e);
        }

        Proxy::getInstance()->registerClass($apiClassName);
        $pluginMetadata = Proxy::getInstance()->getMetadata()[$apiClassName] ?? [];

        // The proxy has already determined which methods should be ignored, so we just use the list it came up with.
        $methodInfoArray = [];
        foreach ($pluginMetadata as $methodName => $metadataMethod) {
            if ($methodName === '__documentation') {
                continue;
            }
            $result = $this->buildJsonInfoForApiMethod($reflectionClass, $methodName, $metadataMethod);
            if (!empty($result)) {
                $methodInfoArray[] = $result;
            }
        }

        return $methodInfoArray;
    }

    /**
     * Build the array of info about a specific API endpoint.
     *
     * @param \ReflectionClass $reflectionClass Reflection class used to get the method data defined in the signature.
     * @param string $methodName Name of the method being processed.
     * @param array $methodMetadata Metadata from the Proxy class which can be used to enhance the reflection data.
     *
     * @return array Collection of key details about the API endpoint.
     * @throws \ReflectionException If the method cannot be found on the class.
     */
    public function buildJsonInfoForApiMethod(\ReflectionClass $reflectionClass, string $methodName, array $methodMetadata): array
    {
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        if (AnnotationGenerator::shouldApiMethodBeIgnored($reflectionMethod)) {
            return [];
        }

        $doc = $reflectionMethod->getDocComment() ?: '';

        $signatureString = 'public function ' . $methodName . '(';
        $paramsString = '';
        $signature = $this->buildMethodSignatureUsingReflection($reflectionMethod);
        foreach ($signature['params'] as $param) {
            $paramsString .= !empty($param['php_type']) ? $param['php_type'] . ' ' : '';
            $paramsString .= $param['name'];
            $paramsString .= $param['default'] !== null ? ' = ' . (!is_string($param['default']) ? json_encode($param['default']) : $param['default']) : '';
            $paramsString .= ', ';
        }
        $paramsString = rtrim($paramsString, ', ');
        $signatureString .= $paramsString . ')';
        if (!empty($signature['return']) && !empty($signature['return']['php_type'])) {
            $signatureString .= ': ' . ($signature['return']['nullable'] ? '?' : '') . $signature['return']['php_type'];
        }

        $fileName = $reflectionMethod->getFileName();
        $fileName = str_replace(PIWIK_DOCUMENT_ROOT . '/', '', $fileName);

        return [
            'fqcn' => $reflectionClass->getName(),
            'method' => $methodName,
            'source' => ['file' => $fileName, 'start' => $reflectionMethod->getStartLine(), 'end' => $reflectionMethod->getEndLine()],
            'header' => $signatureString,
            'signature' => $signature,
            'docblock_raw' => $doc,
        ];
    }

    /**
     * Use reflection to build up information about the method signature, such as parameter and return type details.
     *
     * @param \ReflectionMethod $reflectionMethod The reflection method which can provide all the necessary info.
     *
     * @return array[] Collection of parameter and return type details, such as php type and whether they're nullable.
     */
    public function buildMethodSignatureUsingReflection(\ReflectionMethod $reflectionMethod)
    {
        $params = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $defaultValue = null;
            if ($reflectionParameter->isOptional() && $reflectionParameter->isDefaultValueAvailable()) {
                $defaultValue = $reflectionParameter->getDefaultValue();
                $defaultValue = $defaultValue !== null ? $defaultValue : json_encode($reflectionParameter->getDefaultValue());
            }
            $param = [
                'name' => '$' . $reflectionParameter->getName(),
                'php_type' => $reflectionParameter->hasType() ? strval($reflectionParameter->getType()) : null,
                'required' => !$reflectionParameter->isOptional(),
                'nullable' => $reflectionParameter->hasType() && $reflectionParameter->getType() !== null && $reflectionParameter->getType()->allowsNull(),
                'default' => $defaultValue,
                'byRef' => $reflectionParameter->isPassedByReference(),
                'variadic' => $reflectionParameter->isVariadic(),
            ];

            $params[] = $param;
        }

        $returnType = $reflectionMethod->getReturnType();
        $returnTypeInfo = [
            'php_type' => $returnType ? strval($returnType) : null,
            'nullable' => $returnType !== null && $returnType->allowsNull(),
        ];

        return ['params' => $params, 'return' => $returnTypeInfo];
    }
}
