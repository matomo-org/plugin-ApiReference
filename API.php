<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

use Piwik\Piwik;
use Piwik\Plugins\OpenApiDocs\Generate\MatomoApiDocGenerator;

/**
 * API for plugin OpenApiDocs
 *
 * @method static \Piwik\Plugins\OpenApiDocs\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Get the generated API documentation data for the specified plugin.
     *
     * /index.php?module=API&method=OpenApiDocs.getApiDocumentationJson&plugin=CustomAlerts
     *
     * @param string $plugin Name of the plugin to get the JSON for. E.g. TagManager or CustomerAlerts
     * @param string $format Optional format string to indicate JSON or YAML. Default is JSON
     * @return string | array
     * @throws \Exception
     */
    public function getApiDocumentation(string $plugin, string $format = 'json')
    {
        Piwik::checkUserHasSomeViewAccess();

        $docString = (new MatomoApiDocGenerator())->generatePluginDoc($plugin, $format);
        return strtolower($format) === 'json' ? json_decode($docString, true) : $docString;
    }
}
