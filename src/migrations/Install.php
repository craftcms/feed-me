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
            'fieldMapping' => $this->text(),
            'fieldUnique' => $this->text(),
            'passkey' => $this->string()->notNull(),
            'backup' => $this->boolean()->notNull()->defaultValue(false),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    protected function removeTables(): void
    {
        $this->dropTableIfExists('{{%feedme_feeds}}');
    }
}
