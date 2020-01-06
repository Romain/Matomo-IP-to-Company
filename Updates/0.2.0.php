<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\IPtoCompany;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 0.2.0.
 */
class Updates_0_2_0 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Return database migrations to be executed in this update.
     *
     * Database migrations should be defined here, instead of in `doUpdate()`, since this method is used
     * in the `core:update` command when displaying the queries an update will run. If you execute
     * migrations directly in `doUpdate()`, they won't be displayed to the user. Migrations will be executed in the
     * order as positioned in the returned array.
     *
     * @param Updater $updater
     * @return Migration\Db[]
     */
    public function getMigrations(Updater $updater)
    {
        // many different migrations are available to be used via $this->migration factory
        $migrationCreateTableIpToCompany = $this->migration->db->createTable('ip_to_company', [
            'id' => 'INTEGER NOT NULL AUTO_INCREMENT',
            'ip' => 'VARCHAR( 15 ) NOT NULL',
            'as_number' => 'VARCHAR( 10 ) NULL',
            'as_name' => 'VARCHAR( 200 ) NULL',
            'created_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ], 'ip');
        // you can also define custom SQL migrations. If you need to bind parameters, use `->boundSql()`
        // $migration2 = $this->migration->db->sql($sqlQuery = 'SELECT 1');

        return array(
            $migrationCreateTableIpToCompany
        );
    }

    /**
     * Perform the incremental version update.
     *
     * This method should perform all updating logic. If you define queries in the `getMigrations()` method,
     * you must call {@link Updater::executeMigrations()} here.
     *
     * @param Updater $updater
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
