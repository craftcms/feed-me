<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m230123_152413_set_empty_values migration.
 */
class m230123_152413_set_empty_values extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'setEmptyValues')) {
            $this->addColumn('{{%feedme_feeds}}', 'setEmptyValues', $this->boolean()->defaultValue(false)->notNull()->after('backup'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'setEmptyValues')) {
            $this->dropColumn('{{%feedme_feeds}}', 'setEmptyValues');
        }
    }
}