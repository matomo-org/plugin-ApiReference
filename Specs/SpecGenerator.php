<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs\Specs;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Log\NullLogger;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Annotations\AnnotationGenerator;
use Piwik\SettingsPiwik;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

class SpecGenerator
{
    public function __construct()
    {
        // Set the constant for the current instance's URL
        if(!defined('LOCAL_MATOMO_SERVER_URL')) {
            define('LOCAL_MATOMO_SERVER_URL', SettingsPiwik::getPiwikUrl());
        }
    }

    public function generatePluginDoc(string $pluginName, string $format = 'json'): string
    {
        BaseValidator::check('plugin', $pluginName, [new NotEmpty()]);
        Manager::getInstance()->checkIsPluginActivated($pluginName);

        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $pluginDir = Manager::getInstance()::getPluginDirectory($pluginName);

        // Check if the API class has been annotated and use the generated annotations file if it hasn't
        $pluginAnnotationsSource = $pluginDir . '/API.php';
        $tempGenerator = new Generator(StaticContainer::get(NullLogger::class));
        $openapi = $tempGenerator->generate([
            $pluginAnnotationsSource,
        ]);
        if (trim($openapi->toYaml()) === 'openapi: ' . OpenApi::DEFAULT_VERSION) {
            $pluginAnnotationDir = $pluginDir . '/OpenApi/Annotations';
            $pluginAnnotationPath = $pluginAnnotationDir . '/GeneratedAnnotations.php';
            $pluginAnnotationsSource = $pluginAnnotationPath;
            // If the generated file doesn't exist yet, generate one
            if (!is_dir($pluginAnnotationDir) || !file_exists($pluginAnnotationPath)) {
                (StaticContainer::get(AnnotationGenerator::class))->generatePluginApiAnnotations($pluginName, true);
            }
        }

        $generator = new Generator(StaticContainer::get(LoggerInterface::class));
        $generator->setVersion(OpenApi::DEFAULT_VERSION);

        $openapi = $generator->generate([
            $currentPluginDir . '/Annotations/GlobalApiComponents.php',
            $pluginAnnotationsSource,
        ]);

        $openapi->info->title .= ' for ' . $pluginName . ' plugin';

        return strtolower($format) === 'yaml' ? $openapi->toYaml() : $openapi->toJson();
    }
}
