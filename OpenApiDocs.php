<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

class OpenApiDocs extends \Piwik\Plugin
{
    public function registerEvents(): array
    {
        return [
//            'CronArchive.getArchivingAPIMethodForPlugin' => 'getArchivingAPIMethodForPlugin',
        ];
    }

    // support archiving just this plugin via core:archive
//    public function getArchivingAPIMethodForPlugin(&$method, $plugin)
//    {
//        if ($plugin == 'OpenApiDocs') {
//            $method = 'OpenApiDocs.getExampleArchivedMetric';
//        }
//    }
}
