<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs;

use Piwik\Config;
use Piwik\Log\LoggerInterface;
use Piwik\Plugins\OpenApiDocs\Generation\PluginListProvider;
use Piwik\Plugins\OpenApiDocs\Generation\SpecGenerationService;

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

    public function __construct(
        SpecGenerationService $specGenerationService,
        LoggerInterface $logger,
        ?PluginListProvider $pluginListProvider = null
    ) {
        $this->specGenerationService = $specGenerationService;
        $this->logger = $logger;
        $this->pluginListProvider = $pluginListProvider ?? new PluginListProvider();
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
                    OpenApiDocs::DEFAULT_SPEC_VERSION,
                    true,
                    true
                );
            } catch (\Throwable $e) {
                $this->logger->error(
                    'OpenApiDocs scheduled generation failed for plugin {plugin}: {error}',
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
        return (bool) (Config::getInstance()->OpenApiDocs['enable_spec_generation_task'] ?? 0);
    }
}
