<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference;

use Piwik\Log\LoggerInterface;
use Piwik\Plugins\ApiReference\Generation\PluginListProvider;
use Piwik\Plugins\ApiReference\Generation\SpecGenerationService;

class Tasks extends \Piwik\Plugin\Tasks
{
    /**
     * @var SpecGenerationService
     */
    private $specGenerationService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PluginListProvider
     */
    private $pluginListProvider;
    /**
     * @var Configuration
     */
    private $config;

    public function __construct(
        SpecGenerationService $specGenerationService,
        LoggerInterface $logger,
        ?PluginListProvider $pluginListProvider = null,
        ?Configuration $config = null
    ) {
        $this->specGenerationService = $specGenerationService;
        $this->logger = $logger;
        $this->pluginListProvider = $pluginListProvider ?? new PluginListProvider();
        $this->config = $config ?? new Configuration();
    }

    public function schedule()
    {
        if ($this->isSpecGenerationEnabled()) {
            $this->daily('generateConfiguredPluginSpecs');
        }
    }

    public function generateConfiguredPluginSpecs(): void
    {
        $pluginNames = $this->pluginListProvider->getAllowedPlugins();

        foreach ($pluginNames as $pluginName) {
            try {
                $this->specGenerationService->generateSpecForPlugins(
                    $pluginName,
                    'json',
                    ApiReference::DEFAULT_SPEC_VERSION,
                    true,
                    true
                );
            } catch (\Throwable $e) {
                $this->logger->error(
                    'ApiReference scheduled generation failed for plugin {plugin}: {error}',
                    [
                        'plugin' => $pluginName,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }
    }

    private function isSpecGenerationEnabled(): bool
    {
        return $this->config->specGenerationEnabled();
    }
}
