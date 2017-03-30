<?php
namespace Craft;

class m170330_000000_feedMe_supportCustomDataTypes extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->alterColumn('feedme_feeds', 'feedType', ColumnType::Varchar);
        return true;
    }
}