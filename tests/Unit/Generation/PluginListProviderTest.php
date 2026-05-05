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
use Piwik\Plugin\Manager;
use Piwik\Plugins\OpenApiDocs\Generation\PluginListProvider;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_PluginListProviderTest
 */
class PluginListProviderTest extends TestCase
{
    public function testGetAllowedPluginsIncludesActivatedPluginWhenEligible(): void
    {
        $provider = $this->makeProvider(
            ['HasApi'],
            ['HasApi' => true],
            true
        );

        $this->assertSame(['HasApi'], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsExcludesBlocklistedPlugin(): void
    {
        $provider = $this->makeProvider(
            ['ConnectAccounts'],
            ['ConnectAccounts' => true],
            true
        );

        $this->assertSame([], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsExcludesPluginNotInFilesystem(): void
    {
        $provider = $this->makeProvider(
            ['InactivePlugin'],
            ['InactivePlugin' => false],
            true
        );

        $this->assertSame([], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsExcludesPluginWithoutApiFile(): void
    {
        $provider = $this->makeProvider(
            ['NoApi'],
            ['NoApi' => true],
            false
        );

        $this->assertSame([], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsAppliesEventUpdates(): void
    {
        $provider = $this->makeProvider(
            ['HasApi', 'Login'],
            ['HasApi' => true, 'Login' => true],
            ['HasApi' => true, 'Login' => true],
            static function (string $eventName, array $params): void {
                $pluginNames = &$params[0];
                $pluginNames[] = 'Login';
            }
        );

        $this->assertSame(['HasApi', 'Login'], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsReturnsEmptyListWhenNoPluginsInstalled(): void
    {
        $provider = $this->makeProvider([], [], false);

        $this->assertSame([], $provider->getAllowedPlugins());
    }

    public function testGetAllowedPluginsDropsInvalidEventUpdatesButKeepsEventAddedPlugins(): void
    {
        $provider = $this->makeProvider(
            ['HasApi', 'Login', 'InactivePlugin', 'NoApi', 'ConnectAccounts'],
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
            static function (string $eventName, array $params): void {
                $pluginNames = &$params[0];
                $pluginNames[] = 'Login';
                $pluginNames[] = 'InactivePlugin';
                $pluginNames[] = 'NoApi';
                $pluginNames[] = 'ConnectAccounts';
                $pluginNames[] = 123;
            }
        );

        $this->assertSame(['HasApi', 'Login', 'InactivePlugin'], $provider->getAllowedPlugins());
    }

    /**
     * @param string[] $activatedPlugins
     * @param array<string, bool> $inFilesystemByPlugin
     * @param bool|array<string, bool> $hasApiFile
     */
    private function makeProvider(
        array $activatedPlugins,
        array $inFilesystemByPlugin,
        $hasApiFile,
        ?callable $postEventCallback = null
    ): PluginListProvider {
        $pluginManager = $this->createConfiguredMock(Manager::class, [
            'getActivatedPlugins' => $activatedPlugins,
            'getPluginsLoadedAndActivated' => [],
        ]);

        $pluginManager->method('isPluginInFilesystem')
            ->willReturnCallback(static function (string $pluginName) use ($inFilesystemByPlugin): bool {
                return $inFilesystemByPlugin[$pluginName] ?? false;
            });

        $provider = $this->getMockBuilder(PluginListProvider::class)
            ->setConstructorArgs([$pluginManager])
            ->onlyMethods(['pluginHasApiFile', 'postEvent'])
            ->getMock();

        $provider->method('pluginHasApiFile')
            ->willReturnCallback(static function (string $pluginName) use ($hasApiFile): bool {
                if (is_array($hasApiFile)) {
                    return $hasApiFile[$pluginName] ?? false;
                }

                return $hasApiFile;
            });

        if ($postEventCallback) {
            $provider->method('postEvent')
                ->willReturnCallback($postEventCallback);
        }

        return $provider;
    }
}
