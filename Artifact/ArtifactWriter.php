<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\Artifact;

use Piwik\Filesystem;

class ArtifactWriter
{
    /**
     * @return false|int
     */
    public function writeFile(string $filePath, string $contents)
    {
        Filesystem::mkdir(dirname($filePath));

        return file_put_contents($filePath, $contents);
    }
}
