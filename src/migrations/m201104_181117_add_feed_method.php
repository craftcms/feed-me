<?php

namespace craft\feedme\migrations;

use Craft;
use craft\db\Query;
use craft\db\Migration;

/**
 * m201104_181117_add_feed_method migration.
 */
class m201104_181117_add_feed_method extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {


        if (!$this->db->columnExists('{{%feedme_feeds}}', 'feedMethod')) {
            $this->addColumn('{{%feedme_feeds}}', 'feedMethod', $this->string()->after('feedType'));

            $feeds = (new Query())
                ->select(['*'])
                ->from(['{{%feedme_feeds}}'])
                ->all();

            foreach ($feeds as $i => $feed) {
                $this->update('{{%feedme_feeds}}', ['feedMethod' => 'GET'], ['id' => $feed['id']]);
            }
        }

        // Place migration code here...
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->db->columnExists('{{%feedme_feeds}}', 'feedMethod')) {
            $this->dropColumn('{{%feedme_feeds}}', 'feedMethod');
            return true;
        } else {
            echo "m201104_181117_add_feed_method cannot be reverted.\n";
            return false;
        }
    }
}
