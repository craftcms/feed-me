<?php

namespace craft\feedme\migrations;

use Craft;
use craft\db\Migration;

/**
 * m210313_164741_migration_for_total_page_number migration.
 */
class m210313_164741_migration_for_total_page_number extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'paginationTotalNode')) {
            $this->addColumn('{{%feedme_feeds}}', 'paginationTotalNode', $this->text()->after('duplicateHandle'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m210313_164741_migration_for_total_page_number cannot be reverted.\n";
        return false;
    }
}
