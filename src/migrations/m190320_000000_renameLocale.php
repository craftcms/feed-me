<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

class m190320_000000_renameLocale extends Migration
{
    public function safeUp(): bool
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'locale')) {
            $this->renameColumn('{{%feedme_feeds}}', 'locale', 'siteId');
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m190320_000000_renameLocale cannot be reverted.\n";

        return false;
    }
}
