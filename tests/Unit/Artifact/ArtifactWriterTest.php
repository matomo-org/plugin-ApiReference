<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Unit\Artifact;

require_once PIWIK_INCLUDE_PATH . '/plugins/ApiReference/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ApiReference\Artifact\ArtifactWriter;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_ArtifactWriterTest
 */
class ArtifactWriterTest extends TestCase
{
    private $temporaryDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryDirectory = sys_get_temp_dir() . '/openapidocs_artifact_writer_' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->temporaryDirectory)) {
            $files = scandir($this->temporaryDirectory);
            if (is_array($files)) {
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..') {
                        continue;
                    }

                    @unlink($this->temporaryDirectory . '/' . $file);
                }
            }

            @rmdir($this->temporaryDirectory . '/nested');
            @rmdir($this->temporaryDirectory);
        }

        parent::tearDown();
    }

    public function testWriteFileCreatesDirectoryAndWritesContents(): void
    {
        $writer = new ArtifactWriter();
        $filePath = $this->temporaryDirectory . '/nested/example.json';

        $result = $writer->writeFile($filePath, '{"status":"ok"}');

        $this->assertIsInt($result);
        $this->assertSame('{"status":"ok"}', file_get_contents($filePath));
    }
}
