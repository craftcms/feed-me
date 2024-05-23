<?php

namespace craft\feedme\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240523_145259_field_mapping_column_change migration.
 */
class m240523_145259_field_mapping_column_change extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'fieldMapping')) {
            $this->alterColumn('{{%feedme_feeds}}', 'fieldMapping', $this->mediumText());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'fieldMapping')) {
            $this->alterColumn('{{%feedme_feeds}}', 'fieldMapping', $this->text());
        }

        return true;
    }
}
