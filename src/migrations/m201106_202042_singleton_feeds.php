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
    public function safeUp(): bool
    {
        $this->addColumn('{{%feedme_feeds}}', 'singleton', $this->boolean()->notNull()->defaultValue(false)->after('sortOrder'));

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn('{{%feedme_feeds}}', 'singleton');

        return true;
    }
}
