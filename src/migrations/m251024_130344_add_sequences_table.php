<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m251024_130344_add_sequences_table migration.
 */
class m251024_130344_add_sequences_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable('{{%feedme_sequences}}', [
            'id' => $this->primaryKey(),
            'key' => $this->string()->notNull(),
            'feedId' => $this->integer()->notNull(),
            'options' => $this->text(),
            'timestamp' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex('idx_sequence_key', '{{%feedme_sequences}}', 'key');
        $this->createIndex('idx_sequence_feedId', '{{%feedme_sequences}}', 'feedId');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%feedme_sequences}}');

        return true;
    }
}
