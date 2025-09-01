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
use Piwik\Plugin\Manager;
use Piwik\SettingsPiwik;
use Piwik\Validators\BaseValidator;
use Piwik\Validators\NotEmpty;

class SpecGenerator
{
    public function __construct()
    {
        // Set the constant for the current instance's URL
        if (!defined('LOCAL_MATOMO_SERVER_URL')) {
            define('LOCAL_MATOMO_SERVER_URL', SettingsPiwik::getPiwikUrl());
        }
    }

    public function generatePluginDoc(string $pluginName, string $format = 'json'): string
    {
        BaseValidator::check('plugin', $pluginName, [new NotEmpty()]);
        Manager::getInstance()->checkIsPluginActivated($pluginName);

        $currentPluginDir = Manager::getInstance()::getPluginDirectory('OpenApiDocs');
        $pluginDir = Manager::getInstance()::getPluginDirectory($pluginName);

        $generator = new Generator(StaticContainer::get(LoggerInterface::class));
        $generator->setVersion(OpenApi::DEFAULT_VERSION);
        $openapi = $generator->generate([
            $currentPluginDir . '/Annotations/GlobalApiComponents.php',
            $pluginDir . '/API.php',
        ]);

        return strtolower($format) === 'yaml' ? $openapi->toYaml() : $openapi->toJson();
    }
}
