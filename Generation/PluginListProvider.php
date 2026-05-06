<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\Generation;

use Piwik\Piwik;
use Piwik\Plugin\Manager;

class PluginListProvider
{
    /**
     * @var Manager
     */
    private $pluginManager;

    public function __construct(?Manager $pluginManager = null)
    {
        $this->pluginManager = $pluginManager ?? Manager::getInstance();
    }

    /**
     * @return string[]
     */
    public function getAllowedPlugins(): array
    {
        $pluginNames = array_values($this->pluginManager->getActivatedPlugins());

        $this->dispatchUpdatePluginListEvent($pluginNames);

        $pluginNames = array_values(array_unique($pluginNames));

        return array_values(array_filter($pluginNames, function ($pluginName): bool {
            return is_string($pluginName) && $this->shouldIncludeEventProvidedPlugin($pluginName);
        }));
    }

    private function shouldIncludeEventProvidedPlugin(string $pluginName): bool
    {
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
        $this->postEvent('OpenApiDocs.updatePluginList', [&$pluginNames]);
    }

    /**
     * @param array<int, mixed> $params
     */
    protected function postEvent(string $eventName, array $params): void
    {
        Piwik::postEvent($eventName, $params);
    }
}
