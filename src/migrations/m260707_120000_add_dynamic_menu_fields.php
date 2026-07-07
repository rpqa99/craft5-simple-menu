<?php

namespace remoteprogrammer\simplerpmenu\migrations;

use Craft;
use craft\db\Migration;

/**
 * m260707_120000_add_dynamic_menu_fields migration.
 */
class m260707_120000_add_dynamic_menu_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%simplerpmenu_items}}';

        if (!$this->db->columnExists($tableName, 'dropdownType')) {
            $this->addColumn($tableName, 'dropdownType', $this->string(50)->notNull()->defaultValue('static'));
        }

        if (!$this->db->columnExists($tableName, 'dynamicSource')) {
            $this->addColumn($tableName, 'dynamicSource', $this->string(50)->null());
        }

        if (!$this->db->columnExists($tableName, 'dynamicSourceId')) {
            $this->addColumn($tableName, 'dynamicSourceId', $this->integer()->null());
        }

        if (!$this->db->columnExists($tableName, 'maxLevel')) {
            $this->addColumn($tableName, 'maxLevel', $this->integer()->notNull()->defaultValue(1));
        }

        if (!$this->db->columnExists($tableName, 'dynamicSettings')) {
            $this->addColumn($tableName, 'dynamicSettings', $this->text()->null());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $tableName = '{{%simplerpmenu_items}}';

        if ($this->db->columnExists($tableName, 'dropdownType')) {
            $this->dropColumn($tableName, 'dropdownType');
        }

        if ($this->db->columnExists($tableName, 'dynamicSource')) {
            $this->dropColumn($tableName, 'dynamicSource');
        }

        if ($this->db->columnExists($tableName, 'dynamicSourceId')) {
            $this->dropColumn($tableName, 'dynamicSourceId');
        }

        if ($this->db->columnExists($tableName, 'maxLevel')) {
            $this->dropColumn($tableName, 'maxLevel');
        }

        if ($this->db->columnExists($tableName, 'dynamicSettings')) {
            $this->dropColumn($tableName, 'dynamicSettings');
        }

        return true;
    }
}
