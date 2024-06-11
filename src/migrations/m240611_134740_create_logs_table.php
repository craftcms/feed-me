<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

/**
 * m240611_134740_create_logs_table migration.
 */
class m240611_134740_create_logs_table extends Migration
{
    public const LOG_TABLE = '{{%feedme_logs}}';

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->tableExists(self::LOG_TABLE)) {
            return true;
        }

        // @see \craft\feedme\migrations\m240611_134740_create_logs_table
        $this->createTable(self::LOG_TABLE, [
            'id' => $this->bigPrimaryKey(),
            'level' => $this->integer(),
            'category' => $this->string(),
            'log_time' => $this->double(),
            'prefix' => $this->text(),
            'message' => $this->text(),
        ]);

        $this->createIndex('idx_log_level', self::LOG_TABLE, 'level');
        $this->createIndex('idx_log_category', self::LOG_TABLE, 'category');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->db->tableExists(self::LOG_TABLE)) {
            $this->dropTable(self::LOG_TABLE);
        }

        return true;
    }
}
