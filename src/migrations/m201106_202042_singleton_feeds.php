<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m201106_202042_singleton_feeds migration.
 */
class m201106_202042_singleton_feeds extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'singleton')) {
            $this->addColumn('{{%feedme_feeds}}', 'singleton', $this->boolean()->notNull()->defaultValue(false)->after('sortOrder'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%feedme_feeds}}', 'singleton');
    }
}
