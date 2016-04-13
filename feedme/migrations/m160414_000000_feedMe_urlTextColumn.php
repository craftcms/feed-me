<?php
namespace Craft;

class m160414_000000_feedMe_urlTextColumn extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->alterColumn('feedme_feeds', 'feedUrl', ColumnType::Text);

        return true;
    }
}
