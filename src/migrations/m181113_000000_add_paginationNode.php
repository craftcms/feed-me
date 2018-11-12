<?php
namespace verbb\navigation\migrations;

use verbb\navigation\elements\Node;

use Craft;
use craft\db\Migration;

class m181113_000000_add_paginationNode extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%feedme_feeds}}', 'paginationNode', $this->text()->after('duplicateHandle'));

        return true;
    }

    public function safeDown()
    {
        echo "m181113_000000_add_paginationNode cannot be reverted.\n";

        return false;
    }
}
