<?php
namespace Craft;

class m150503_144613_feedMe_allowPrimaryElementNull extends BaseMigration
{
    public function safeUp()
    {
        craft()->db->createCommand()->alterColumn('feedme_feeds', 'primaryElement', array(
            'column' => ColumnType::Varchar,
            'null' => true
        ));

        return true;
    }
}
