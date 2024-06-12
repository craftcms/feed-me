<?php

namespace craft\feedme\migrations;

use craft\db\Migration;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp(): bool
    {
        $this->createTables();

        return true;
    }

    public function safeDown(): bool
    {
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables(): void
    {
        $this->createTable('{{%feedme_feeds}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'feedUrl' => $this->text()->notNull(),
            'feedType' => $this->string(),
            'primaryElement' => $this->string(),
            'elementType' => $this->string()->notNull(),
            'elementGroup' => $this->text(),
            'siteId' => $this->string(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'singleton' => $this->boolean()->notNull()->defaultValue(false),
            'duplicateHandle' => $this->text(),
            'updateSearchIndexes' => $this->boolean()->notNull()->defaultValue(true),
            'paginationNode' => $this->text(),
            'fieldMapping' => $this->mediumText(),
            'fieldUnique' => $this->text(),
            'passkey' => $this->string()->notNull(),
            'backup' => $this->boolean()->notNull()->defaultValue(false),
            'setEmptyValues' => $this->boolean()->notNull()->defaultValue(false),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

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
    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists('{{%feedme_feeds}}');
        $this->dropTableIfExists('{{%feedme_logs}}');
    }
}
