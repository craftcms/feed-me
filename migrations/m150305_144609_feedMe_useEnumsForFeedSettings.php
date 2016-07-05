<?php
namespace Craft;

class m150305_144609_feedMe_useEnumsForFeedSettings extends BaseMigration
{
    public function safeUp()
    {
        // Set fields to be Enum before things get too complicated!
        craft()->db->createCommand()->alterColumn('feedme_feeds', 'duplicateHandle', array(
            'values' => array(
                FeedMe_Duplicate::Add,
                FeedMe_Duplicate::Update,
                FeedMe_Duplicate::Delete,
            ),
            'column' => 'enum'
        ));

        craft()->db->createCommand()->alterColumn('feedme_feeds', 'feedType', array(
            'values' => array(
                FeedMe_FeedType::XML,
                FeedMe_FeedType::RSS,
                FeedMe_FeedType::ATOM,
                FeedMe_FeedType::JSON,
            ),
            'column' => 'enum'
        ));

        return true;
    }
}
