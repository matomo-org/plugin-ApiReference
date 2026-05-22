<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ApiReference;

use Piwik\Config;

class Configuration
{
    public const DEFAULT_ENABLE_SPEC_GENERATION = 1;
    public const KEY_ENABLE_SPEC_GENERATION = 'enable_spec_generation_task';

    public function install()
    {
        $config = $this->getConfig();

        $apiReferenceConfig = $config->ApiReference;
        if (empty($apiReferenceConfig)) {
            $apiReferenceConfig = array();
        }

        // we make sure to set a value only if none has been configured yet, eg in common config.
        if (!array_key_exists(self::KEY_ENABLE_SPEC_GENERATION, $apiReferenceConfig)) {
            $apiReferenceConfig[self::KEY_ENABLE_SPEC_GENERATION] = self::DEFAULT_ENABLE_SPEC_GENERATION;
        }

        $config->ApiReference = $apiReferenceConfig;
        $config->forceSave();
    }

    public function uninstall()
    {
        $config = $this->getConfig();
        $config->ApiReference = array();
        $config->forceSave();
    }


    public function specGenerationEnabled()
    {
        $value = $this->getConfigValue(self::KEY_ENABLE_SPEC_GENERATION, self::DEFAULT_ENABLE_SPEC_GENERATION);

        if ($value === false || $value === '' || $value === null) {
            $value = self::DEFAULT_ENABLE_SPEC_GENERATION;
        }

        return (bool) $value;
    }

    private function getConfigValue($name, $default)
    {
        $config = $this->getConfig();
        $attribution = $config->ApiReference;
        if (isset($attribution[$name])) {
            return $attribution[$name];
        }
        return $default;
    }

    private function getConfig()
    {
        return Config::getInstance();
    }
}
