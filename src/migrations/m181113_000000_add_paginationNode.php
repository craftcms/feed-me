<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

class m181113_000000_add_paginationNode extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'paginationNode')) {
            $this->addColumn('{{%feedme_feeds}}', 'paginationNode', $this->text()->after('duplicateHandle'));
        }

        return true;
    }

    public function safeDown(): bool
    {
        echo "m181113_000000_add_paginationNode cannot be reverted.\n";

        return false;
    }
}
