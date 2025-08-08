<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\OpenApiDocs\Renderer;

use Piwik\API\ApiRenderer;
use Piwik\Common;
use Piwik\DataTable\Renderer;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class Yaml extends ApiRenderer
{
    public static function sendYamlHeader()
    {
        Common::sendHeader('Content-Type: text/yaml; charset=utf-8', true);
    }

    public function sendHeader()
    {
        self::sendYamlHeader();
    }

    public function renderArray($array)
    {
        return SymfonyYaml::dump($array);
    }

    public function renderException($message, $exception)
    {
        return SymfonyYaml::dump(['result' => 'error', 'message' => $exception->getMessage()]);
    }

    public function renderSuccess($message)
    {
        return SymfonyYaml::dump(['success' => true, 'message' => $message]);
    }

    public function renderDataTable($dataTable)
    {
        return $dataTable->getFirstRow()->getColumn(0);
    }
}