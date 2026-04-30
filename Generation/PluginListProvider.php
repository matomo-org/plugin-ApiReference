<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Generation;

use Piwik\Plugin\Manager;
use Piwik\Piwik;
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;

class PluginListProvider
{
    /**
     * @return string[]
     */
    public static function getPluginsForSpecGeneration(): array
    {
        $pluginNames = [];

        foreach (Manager::getInstance()->getInstalledPluginsName() as $pluginName) {
            if (!self::shouldIncludePlugin($pluginName)) {
                continue;
            }

            $pluginNames[] = $pluginName;
        }

        self::dispatchUpdatePluginListEvent($pluginNames);

        return array_values(array_unique($pluginNames));
    }

    private static function shouldIncludePlugin(string $pluginName): bool
    {
        if (in_array($pluginName, OpenApiDocs::PLUGIN_BLOCKLIST, true)) {
            return false;
        }

        $pluginManager = Manager::getInstance();

        if (
            !$pluginManager->isPluginActivated($pluginName)
            || !$pluginManager->isPluginInFilesystem($pluginName)
        ) {
            return false;
        }

        return is_file(Manager::getPluginDirectory($pluginName) . '/API.php');
    }

    /**
     * @param string[] $pluginNames
     */
    private static function dispatchUpdatePluginListEvent(array &$pluginNames): void
    {
        Piwik::postEvent('OpenApiDocs.updatePluginList', [&$pluginNames]);
    }
}
