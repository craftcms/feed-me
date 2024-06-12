<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m240611_134740_create_logs_table migration.
 */
class m240611_134740_create_logs_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // @see \craft\feedme\migrations\m240611_134740_create_logs_table
        $this->createTable('{{%feedme_logs}}', [
            'id' => $this->bigPrimaryKey(),
            'level' => $this->integer(),
            'category' => $this->string(),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ]);

        $this->createIndex('idx_log_level', '{{%feedme_logs}}', 'level');
        $this->createIndex('idx_log_category', '{{%feedme_logs}}', 'category');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%feedme_logs}}');

        return true;
    }
}
