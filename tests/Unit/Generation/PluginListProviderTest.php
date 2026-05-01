<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit\Generation;

require_once PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\EventDispatcher;
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Generation\PluginListProvider;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_PluginListProviderTest
 */
class PluginListProviderTest extends TestCase
{
    public function testGetPluginsForSpecGenerationIncludesPluginWhenEligible(): void
    {
        $provider = $this->makeProvider(
            ['HasApi'],
            ['HasApi' => true],
            ['HasApi' => true],
            true
        );

        $this->assertSame(['HasApi'], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationExcludesBlocklistedPlugin(): void
    {
        $provider = $this->makeProvider(
            ['ConnectAccounts'],
            ['ConnectAccounts' => true],
            ['ConnectAccounts' => true],
            true
        );

        $this->assertSame([], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationExcludesInactivePlugin(): void
    {
        $provider = $this->makeProvider(
            ['InactivePlugin'],
            ['InactivePlugin' => false],
            ['InactivePlugin' => true],
            true
        );

        $this->assertSame([], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationExcludesPluginNotInFilesystem(): void
    {
        $provider = $this->makeProvider(
            ['MissingPlugin'],
            ['MissingPlugin' => true],
            ['MissingPlugin' => false],
            true
        );

        $this->assertSame([], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationExcludesPluginWithoutApiFile(): void
    {
        $provider = $this->makeProvider(
            ['NoApi'],
            ['NoApi' => true],
            ['NoApi' => true],
            false
        );

        $this->assertSame([], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationAppliesEventUpdates(): void
    {
        $provider = $this->makeProvider(
            ['HasApi', 'Login'],
            ['HasApi' => true, 'Login' => true],
            ['HasApi' => true, 'Login' => true],
            ['HasApi' => true, 'Login' => true],
            static function (EventDispatcher $eventDispatcher): void {
                $eventDispatcher->addObserver('OpenApiDocs.updatePluginList', function (&$pluginNames): void {
                    $pluginNames[] = 'Login';
                });
            }
        );

        $this->assertSame(['HasApi', 'Login'], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationReturnsEmptyListWhenNoPluginsInstalled(): void
    {
        $provider = $this->makeProvider([], [], [], false);

        $this->assertSame([], $provider->getPluginsForSpecGeneration());
    }

    public function testGetPluginsForSpecGenerationDropsInvalidEventUpdatesButKeepsUnactivatedInstalledPlugins(): void
    {
        $provider = $this->makeProvider(
            ['HasApi', 'Login', 'InactivePlugin', 'NoApi', 'ConnectAccounts'],
            [
                'HasApi' => true,
                'Login' => true,
                'InactivePlugin' => false,
                'NoApi' => true,
                'ConnectAccounts' => true,
            ],
            [
                'HasApi' => true,
                'Login' => true,
                'InactivePlugin' => true,
                'NoApi' => true,
                'ConnectAccounts' => true,
            ],
            [
                'HasApi' => true,
                'Login' => true,
                'InactivePlugin' => true,
                'NoApi' => false,
                'ConnectAccounts' => true,
            ],
            static function (EventDispatcher $eventDispatcher): void {
                $eventDispatcher->addObserver('OpenApiDocs.updatePluginList', function (&$pluginNames): void {
                    $pluginNames[] = 'Login';
                    $pluginNames[] = 'InactivePlugin';
                    $pluginNames[] = 'NoApi';
                    $pluginNames[] = 'ConnectAccounts';
                    $pluginNames[] = 123;
                });
            }
        );

        $this->assertSame(['HasApi', 'Login', 'InactivePlugin'], $provider->getPluginsForSpecGeneration());
    }

    /**
     * @param string[] $installedPlugins
     * @param array<string, bool> $activatedByPlugin
     * @param array<string, bool> $inFilesystemByPlugin
     * @param bool|array<string, bool> $hasApiFile
     */
    private function makeProvider(
        array $installedPlugins,
        array $activatedByPlugin,
        array $inFilesystemByPlugin,
        $hasApiFile,
        ?callable $configureEventDispatcher = null
    ): PluginListProvider {
        $pluginManager = $this->createConfiguredMock(Manager::class, [
            'getInstalledPluginsName' => $installedPlugins,
            'getPluginsLoadedAndActivated' => [],
        ]);

        $pluginManager->method('isPluginActivated')
            ->willReturnCallback(static function (string $pluginName) use ($activatedByPlugin): bool {
                return $activatedByPlugin[$pluginName] ?? false;
            });

        $pluginManager->method('isPluginInFilesystem')
            ->willReturnCallback(static function (string $pluginName) use ($inFilesystemByPlugin): bool {
                return $inFilesystemByPlugin[$pluginName] ?? false;
            });

        if ($configureEventDispatcher) {
            $eventDispatcher = new EventDispatcher($pluginManager, []);
            $configureEventDispatcher($eventDispatcher);
        } else {
            $eventDispatcher = $this->createMock(EventDispatcher::class);
        }

        $provider = $this->getMockBuilder(PluginListProvider::class)
            ->setConstructorArgs([$pluginManager, $eventDispatcher])
            ->onlyMethods(['pluginHasApiFile'])
            ->getMock();

        $provider->method('pluginHasApiFile')
            ->willReturnCallback(static function (string $pluginName) use ($hasApiFile): bool {
                if (is_array($hasApiFile)) {
                    return $hasApiFile[$pluginName] ?? false;
                }

                return $hasApiFile;
            });

        return $provider;
    }
}
