<?php
namespace verbb\feedme\migrations;

use craft\db\Migration;

class Install extends Migration
{
    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->createTables();

        return true;
    }

    public function safeDown()
    {
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $this->createTable('{{%feedme_feeds}}', [
            'id'                   => $this->primaryKey(),
            'name'                 => $this->string()->notNull(),
            'feedUrl'              => $this->text()->notNull(),
            'feedType'             => $this->string(),
            'primaryElement'       => $this->string(),
            'elementType'          => $this->string()->notNull(),
            'elementGroup'         => $this->text(),
            'siteId'               => $this->string(),
            'duplicateHandle'      => $this->text(),
            'fieldMapping'         => $this->text(),
            'fieldUnique'          => $this->text(),
            'passkey'              => $this->string()->notNull(),
            'backup'               => $this->boolean()->notNull()->defaultValue(0),

            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid'         => $this->uid(),
        ]);
    }

    protected function removeTables()
    {
        $this->dropTableIfExists('{{%feedme_feeds}}');
    }
}
