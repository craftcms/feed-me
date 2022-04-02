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
    public function safeUp(): bool
    {
        $this->addColumn('{{%feedme_feeds}}', 'updateSearchIndexes', $this->boolean()->notNull()->defaultValue(true)->after('duplicateHandle'));
        $this->update('{{%feedme_feeds}}', array('updateSearchIndexes' => true));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%feedme_feeds}}', 'updateSearchIndexes');

        return true;
    }
}
