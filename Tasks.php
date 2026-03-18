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

    public function __construct(SpecGenerationService $specGenerationService, LoggerInterface $logger)
    {
        $this->specGenerationService = $specGenerationService;
        $this->logger = $logger;
    }

    public function schedule()
    {
        if ($this->isSpecGenerationEnabled()) {
            $this->weekly('generateConfiguredPluginSpecs');
        }
    }

    public function generateConfiguredPluginSpecs(): void
    {
        $pluginNames = require __DIR__ . '/config/plugins.php';

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
