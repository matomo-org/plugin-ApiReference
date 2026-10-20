<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\ApiReference\ApiReference;

/**
 * @group ApiReference
 * @group ApiReference_Unit
 * @group ApiReference_ApiReferenceTest
 */
class ApiReferenceTest extends TestCase
{
    public function testThrowPluginNotFoundReportsTheMissingPluginAsNotFound(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The plugin NotARealPlugin was not found.');
        $this->expectExceptionCode(404);

        ApiReference::throwPluginNotFound('NotARealPlugin');
    }
}
