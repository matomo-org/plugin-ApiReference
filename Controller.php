<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference;

use Piwik\Piwik;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function swagger(): string
    {
        Piwik::checkUserHasSomeViewAccess();

        $view = new View('@ApiReference/swagger');
        $this->setBasicVariablesView($view);

        return $view->render();
    }
}
