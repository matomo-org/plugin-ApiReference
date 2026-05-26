<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Fixtures;

use Piwik\Plugins\ApiReference\ApiReference;
use Piwik\Plugins\ApiReference\Specs\PathResolver;
use Piwik\Tests\Framework\Fixture;
use Piwik\Updater;

class SwaggerPageFixture extends Fixture
{
    public $dateTime = '2010-01-03 00:00:00';
    public $idSite = 1;
    private bool $hadOriginalSpecFixture = false;
    private ?string $originalSpecFixtureContents = null;

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->markApiReferenceAsInstalled();
        $this->writeOpenApiSpecFixtures();
    }

    public function tearDown(): void
    {
        $this->removeOpenApiSpecFixtures();
    }

    private function setUpWebsite(): void
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function writeOpenApiSpecFixtures(): void
    {
        $resolver = new PathResolver();
        $specDirectory = $resolver->getSpecDirectory();
        $specPath = $this->getBandwidthSpecPath();

        if (!is_dir($specDirectory)) {
            mkdir($specDirectory, 0777, true);
        }

        if (is_file($specPath)) {
            $this->hadOriginalSpecFixture = true;
            $originalContents = file_get_contents($specPath);
            $this->originalSpecFixtureContents = $originalContents === false ? null : $originalContents;
        }

        copy($this->getBandwidthSpecFixturePath(), $specPath);
    }

    private function markApiReferenceAsInstalled(): void
    {
        (new Updater())->markComponentSuccessfullyUpdated('ApiReference', '5.0.0');
    }

    private function removeOpenApiSpecFixtures(): void
    {
        $specPath = $this->getBandwidthSpecPath();

        if ($this->hadOriginalSpecFixture) {
            file_put_contents($specPath, $this->originalSpecFixtureContents ?? '');
        } elseif (is_file($specPath)) {
            unlink($specPath);
        }
    }

    private function getBandwidthSpecPath(): string
    {
        return (new PathResolver())->getSpecFilePath('Bandwidth', ApiReference::DEFAULT_SPEC_VERSION);
    }

    private function getBandwidthSpecFixturePath(): string
    {
        return __DIR__ . '/../Resources/SwaggerPage/Bandwidth_openapi_spec_v1.0.0.json';
    }
}
