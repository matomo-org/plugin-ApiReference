<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs;

 use Piwik\Piwik;
use Piwik\Plugins\OpenApiDocs\Specs\SpecGenerator;

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
     * /index.php?module=API&method=OpenApiDocs.getGeneratedOpenApiSpec&plugin=CustomAlerts
     *
     * @param string $plugin Name of the plugin to get the JSON for. E.g. TagManager or CustomerAlerts
     * @param string $format String to indicate JSON or YAML.
     * @return string | array
     * @throws \Exception
     */
    public function getGeneratedOpenApiSpec(string $plugin, string $format)
    {
        Piwik::checkUserHasSomeViewAccess();

        // Return an error if format is something other than JSON or YAML
        $allowedFormats = ['json', 'yaml'];
        if (!in_array(strtolower($format), $allowedFormats)) {
            throw new \Exception(
                Piwik::translate(
                    'General_ExceptionInvalidReportRendererFormat',
                    [$format, implode(', ', $allowedFormats)]
                )
            );
        }

        $docString = (new SpecGenerator())->generatePluginDoc($plugin, $format);
        return strtolower($format) === 'json' ? json_decode($docString, true) : $docString;
    }
}
