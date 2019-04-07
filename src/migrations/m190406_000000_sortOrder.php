<?php

namespace verbb\feedme\migrations;

use craft\db\Migration;
use craft\db\Query;

class m190406_000000_sortOrder extends Migration
{
    public function safeUp()
    {
        if (!$this->db->columnExists('{{%feedme_feeds}}', 'sortOrder')) {
            $this->addColumn('{{%feedme_feeds}}', 'sortOrder', $this->smallInteger()->unsigned()->after('siteId'));

            $feeds = (new Query())
                ->select(['*'])
                ->from(['{{%feedme_feeds}}'])
                ->all();

            foreach ($feeds as $i => $feed) {
                $this->update('{{%feedme_feeds}}', ['sortOrder' => $i + 1], ['id' => $feed['id']], [], false);
            }
        }

        return true;
    }

    public function safeDown()
    {
        echo "m190406_000000_sortOrder cannot be reverted.\n";

        return false;
    }
}
