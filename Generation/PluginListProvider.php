<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Generation;

use Piwik\EventDispatcher;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\OpenApiDocs;

class PluginListProvider
{
    /**
     * @var Manager
     */
    private $pluginManager;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(?Manager $pluginManager = null, ?EventDispatcher $eventDispatcher = null)
    {
        $this->pluginManager = $pluginManager ?? Manager::getInstance();
        $this->eventDispatcher = $eventDispatcher ?? EventDispatcher::getInstance();
    }

    /**
     * @return string[]
     */
    public function getPluginsForSpecGeneration(): array
    {
        $pluginNames = [];

        foreach ($this->pluginManager->getInstalledPluginsName() as $pluginName) {
            if (!$this->shouldIncludePlugin($pluginName)) {
                continue;
            }

            $pluginNames[] = $pluginName;
        }

        $this->dispatchUpdatePluginListEvent($pluginNames);

        $pluginNames = array_values(array_unique($pluginNames));

        return array_values(array_filter($pluginNames, function ($pluginName): bool {
            return is_string($pluginName) && $this->shouldIncludeEventProvidedPlugin($pluginName);
        }));
    }

    private function shouldIncludePlugin(string $pluginName): bool
    {
        if (in_array($pluginName, OpenApiDocs::PLUGIN_BLOCKLIST, true)) {
            return false;
        }

        if (
            !$this->pluginManager->isPluginActivated($pluginName)
            || !$this->pluginManager->isPluginInFilesystem($pluginName)
        ) {
            return false;
        }

        return $this->pluginHasApiFile($pluginName);
    }

    private function shouldIncludeEventProvidedPlugin(string $pluginName): bool
    {
        if (in_array($pluginName, OpenApiDocs::PLUGIN_BLOCKLIST, true)) {
            return false;
        }

        if (!$this->pluginManager->isPluginInFilesystem($pluginName)) {
            return false;
        }

        return $this->pluginHasApiFile($pluginName);
    }

    protected function pluginHasApiFile(string $pluginName): bool
    {
        return is_file(Manager::getPluginDirectory($pluginName) . '/API.php');
    }

    /**
     * @param string[] $pluginNames
     */
    private function dispatchUpdatePluginListEvent(array &$pluginNames): void
    {
        $this->eventDispatcher->postEvent('OpenApiDocs.updatePluginList', [&$pluginNames]);
    }
}
