<?php

/**
 * Copyright (C) InnoCraft Ltd - All rights reserved.
 *
 * NOTICE:  All information contained herein is, and remains the property of InnoCraft Ltd.
 * The intellectual and technical concepts contained herein are protected by trade secret or copyright law.
 * Redistribution of this information or reproduction of this material is strictly forbidden
 * unless prior written permission is obtained from InnoCraft Ltd.
 *
 * You shall use this code only in accordance with the license agreement obtained from InnoCraft Ltd.
 *
 * @link https://www.innocraft.com/
 * @license For license details see https://www.innocraft.com/license
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
        if (empty($apiReferenceConfig[self::KEY_ENABLE_SPEC_GENERATION])) {
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
