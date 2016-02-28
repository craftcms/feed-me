<?php
namespace Craft;

class m160222_000000_feedMe_addLocale extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->addColumnAfter('feedme_feeds', 'locale', ColumnType::Varchar, 'entrytype');

        return true;
    }
}
