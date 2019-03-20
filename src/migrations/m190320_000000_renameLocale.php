<?php
namespace verbb\feedme\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

class m190320_000000_renameLocale extends Migration
{
    public function safeUp()
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'locale')) {
            MigrationHelper::renameColumn('{{%feedme_feeds}}', 'locale', 'siteId', $this);
        }
    
        return true;
    }

    public function safeDown()
    {
        echo "m190320_000000_renameLocale cannot be reverted.\n";

        return false;
    }
}
