<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m210317_000000_search_indexes migration.
 */
class m210317_000000_search_indexes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'updateSearchIndexes')) {
            $this->addColumn('{{%feedme_feeds}}', 'updateSearchIndexes', $this->boolean()->notNull()->defaultValue(true)->after('duplicateHandle'));
        }

        $this->update('{{%feedme_feeds}}', array('updateSearchIndexes' => true));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%feedme_feeds}}', 'updateSearchIndexes');
    }
}
