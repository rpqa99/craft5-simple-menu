<?php
/**
 * Simple RP Menu plugin for Craft CMS 3.x
 *
 * This is a simple menu to add Singles, Structures, Channels, Categories, Custom menus (with description), etc to your name menu for CRAFT CMS V3.x
 *
 * @link      https://github.com/bedh-rp
 * @copyright Copyright (c) 2022 Bedh Prakash
 */

namespace remoteprogrammer\simplerpmenu\migrations;

use remoteprogrammer\simplerpmenu\SimpleRpMenu;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Simple RP Menu Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    Bedh Prakash
 * @package   SimpleRpMenu
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        // simplerpmenu table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%simplerpmenu}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%simplerpmenu}}',
                [
                    
                    // Columns in the table
                    'id' => $this->primaryKey(),
                    'name' => $this->string(255)->notNull()->defaultValue(''),
                    'handle' => $this->string(255)->notNull()->defaultValue(''),
					'site_id' => $this->integer(11),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        // simplerpmenu_items table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%simplerpmenu_items}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%simplerpmenu_items}}',
                [
                
                    // Columns in the table
                    'id' => $this->primaryKey(),
                    'menu_id' => $this->integer(11)->notNull(),
                    'parent_id' => $this->integer(11)->notNull(),
                    'item_order' => $this->integer(11)->notNull()->defaultValue(0),
                    'name' => $this->string(255)->notNull()->defaultValue(''),
                    'entry_id' => $this->integer(11),
                    'custom_url' => $this->string(255),
                    'noLink' => $this->boolean()->defaultValue(false),
                    'customShortContent' => $this->string(500)->defaultValue(''),
                    'class' => $this->string(255),
                    'class_parent' => $this->string(255),
                    'data_json' => $this->string(255),
                    'target' => $this->string(255),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        // simplerpmenu table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%simplerpmenu}}',
                'name',
                true
            ),
            '{{%simplerpmenu}}',
            'name',
            true
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%simplerpmenu}}',
                'handle',
                true
            ),
            '{{%simplerpmenu}}',
            'handle',
            true
        );

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
        
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // simplerpmenu_items table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%simplerpmenu_items}}', 'menu_id'),
            '{{%simplerpmenu_items}}',
            'menu_id',
            '{{%simplerpmenu}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        // simplerpmenu_items table
        $this->dropTableIfExists('{{%simplerpmenu_items}}');
        
        // simplerpmenu table
        $this->dropTableIfExists('{{%simplerpmenu}}');

    }
}
