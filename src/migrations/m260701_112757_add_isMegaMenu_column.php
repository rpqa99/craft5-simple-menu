<?php

namespace remoteprogrammer\simplerpmenu\migrations;

use Craft;
use craft\db\Migration;

/**
 * m260701_112757_add_isMegaMenu_column migration.
 */
class m260701_112757_add_isMegaMenu_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn('{{%simplerpmenu_items}}', 'isMegaMenu', $this->boolean()->defaultValue(false));
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%simplerpmenu_items}}', 'isMegaMenu');
        return true;
    }
}
