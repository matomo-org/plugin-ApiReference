<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Unit;

require_once PIWIK_INCLUDE_PATH . '/plugins/ApiReference/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugins\ApiReference\Generation\PluginListProvider;
use Piwik\Plugins\ApiReference\Generation\SpecGenerationService;
use Piwik\Plugins\ApiReference\Tasks;
use Piwik\Scheduler\Schedule\Daily;
use Piwik\Tests\Framework\Mock\FakeLogger;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_TasksTest
 */
class TasksTest extends TestCase
{
    /**
     * @var mixed
     */
    private $originalConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalConfig = Config::getInstance()->ApiReference ?? null;
    }

    protected function tearDown(): void
    {
        Config::getInstance()->ApiReference = $this->originalConfig;

        parent::tearDown();
    }

    public function testScheduleDoesNotRegisterTaskWhenDisabled(): void
    {
        Config::getInstance()->ApiReference = ['enable_spec_generation_task' => 0];

        $tasks = new Tasks($this->createMock(SpecGenerationService::class), new FakeLogger());
        $tasks->schedule();

        $this->assertCount(0, $tasks->getScheduledTasks());
    }

    public function testScheduleRegistersDailyTaskWhenEnabled(): void
    {
        Config::getInstance()->ApiReference = ['enable_spec_generation_task' => 1];

        $tasks = new Tasks($this->createMock(SpecGenerationService::class), new FakeLogger());
        $tasks->schedule();

        $scheduledTasks = $tasks->getScheduledTasks();

        $this->assertCount(1, $scheduledTasks);
        $this->assertSame('generateConfiguredPluginSpecs', $scheduledTasks[0]->getMethodName());
        $this->assertInstanceOf(Daily::class, $scheduledTasks[0]->getScheduledTime());
    }

    public function testGenerateConfiguredPluginSpecsLogsPerPluginFailuresAndContinues(): void
    {
        $calledPlugins = [];
        $service = $this->createMock(SpecGenerationService::class);
        $service->expects($this->atLeast(2))
            ->method('generateSpecForPlugins')
            ->willReturnCallback(function (string $pluginName) use (&$calledPlugins): string {
                $calledPlugins[] = $pluginName;

                if ($pluginName === 'RollUpReporting') {
                    throw new \RuntimeException('Foo failed');
                }

                return 'ok';
            });

        $logger = new FakeLogger();
        $pluginListProvider = $this->createMock(PluginListProvider::class);
        $pluginListProvider->method('getAllowedPlugins')
            ->willReturn(['RollUpReporting', 'Login']);

        $tasks = new Tasks($service, $logger, $pluginListProvider);

        $tasks->generateConfiguredPluginSpecs();

        $this->assertSame('RollUpReporting', $calledPlugins[0]);
        $this->assertSame('Login', $calledPlugins[1]);
        $this->assertStringContainsString('ApiReference scheduled generation failed for plugin RollUpReporting: Foo failed', $logger->output);
    }
}
