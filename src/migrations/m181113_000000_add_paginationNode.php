<?php

namespace verbb\feedme\migrations;

use craft\db\Migration;

class m181113_000000_add_paginationNode extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'paginationNode')) {
            $this->addColumn('{{%feedme_feeds}}', 'paginationNode', $this->text()->after('duplicateHandle'));
        }

        return true;
    }

    public function safeDown()
    {
        echo "m181113_000000_add_paginationNode cannot be reverted.\n";

        return false;
    }
}
