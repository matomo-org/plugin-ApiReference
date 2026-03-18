<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\OpenApiDocs\tests\Unit;

require_once PIWIK_INCLUDE_PATH . '/plugins/OpenApiDocs/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Config;
use Piwik\Plugins\OpenApiDocs\Generation\SpecGenerationService;
use Piwik\Plugins\OpenApiDocs\Tasks;
use Piwik\Scheduler\Schedule\Weekly;
use Piwik\Tests\Framework\Mock\FakeLogger;

/**
 * @group OpenApiDocs
 * @group OpenApiDocs_Unit
 * @group OpenApiDocs_TasksTest
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

        $this->originalConfig = Config::getInstance()->OpenApiDocs ?? null;
    }

    protected function tearDown(): void
    {
        Config::getInstance()->OpenApiDocs = $this->originalConfig;

        parent::tearDown();
    }

    public function testScheduleDoesNotRegisterTaskWhenDisabled(): void
    {
        Config::getInstance()->OpenApiDocs = ['enable_spec_generation_task' => 0];

        $tasks = new Tasks($this->createMock(SpecGenerationService::class), new FakeLogger());
        $tasks->schedule();

        $this->assertCount(0, $tasks->getScheduledTasks());
    }

    public function testScheduleRegistersWeeklyTaskWhenEnabled(): void
    {
        Config::getInstance()->OpenApiDocs = ['enable_spec_generation_task' => 1];

        $tasks = new Tasks($this->createMock(SpecGenerationService::class), new FakeLogger());
        $tasks->schedule();

        $scheduledTasks = $tasks->getScheduledTasks();

        $this->assertCount(1, $scheduledTasks);
        $this->assertSame('generateConfiguredPluginSpecs', $scheduledTasks[0]->getMethodName());
        $this->assertInstanceOf(Weekly::class, $scheduledTasks[0]->getScheduledTime());
    }

    public function testGenerateConfiguredPluginSpecsLogsPerPluginFailuresAndContinues(): void
    {
        $service = $this->createMock(SpecGenerationService::class);
        $service->expects($this->atLeastOnce())
            ->method('generateSpecForPlugins')
            ->willReturnCallback(function (string $pluginName): string {
                if ($pluginName === 'RollUpReporting') {
                    throw new \RuntimeException('Foo failed');
                }

                return 'ok';
            });

        $logger = new FakeLogger();
        $tasks = new Tasks($service, $logger);

        $tasks->generateConfiguredPluginSpecs();

        $this->assertStringContainsString('OpenApiDocs scheduled generation failed for plugin RollUpReporting: Foo failed', $logger->output);
    }
}
