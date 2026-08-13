<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\ApiReference;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.0.6.
 */
class Updates_5_0_6 extends PiwikUpdates
{
    /**
     * Description recorded by Piwik::requestTemporarySystemAuthToken() for the tokens this plugin used to request.
     */
    private const TOKEN_DESCRIPTION = 'System generated ApiReference';

    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Spec generation no longer requests a token, so any rows left behind are unused and can go.
     *
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater): array
    {
        $table = Common::prefixTable('user_token_auth');

        return [
            $this->migration->db->boundSql(
                sprintf('DELETE FROM `%s` WHERE `description` = ? AND `system_token` = 1', $table),
                [self::TOKEN_DESCRIPTION]
            ),
        ];
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
