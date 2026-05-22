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
use Piwik\Plugins\ApiReference\Configuration;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_ConfigurationTest
 */
class ConfigurationTest extends TestCase
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

    public function testInstallSetsDefaultWhenConfigKeyIsMissing(): void
    {
        Config::getInstance()->ApiReference = [];

        $configuration = new Configuration();
        $configuration->install();

        $this->assertSame(
            Configuration::DEFAULT_ENABLE_SPEC_GENERATION,
            Config::getInstance()->ApiReference[Configuration::KEY_ENABLE_SPEC_GENERATION]
        );
    }

    public function testInstallPreservesExplicitDisabledValue(): void
    {
        Config::getInstance()->ApiReference = [
            Configuration::KEY_ENABLE_SPEC_GENERATION => 0,
        ];

        $configuration = new Configuration();
        $configuration->install();

        $this->assertSame(0, Config::getInstance()->ApiReference[Configuration::KEY_ENABLE_SPEC_GENERATION]);
    }

    public function testUninstallClearsApiReferenceConfig(): void
    {
        Config::getInstance()->ApiReference = [
            Configuration::KEY_ENABLE_SPEC_GENERATION => 1,
            'another_key' => 'value',
        ];

        $configuration = new Configuration();
        $configuration->uninstall();

        $this->assertSame([], Config::getInstance()->ApiReference);
    }
}
