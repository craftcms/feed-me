<?php

namespace craft\feedme\records;

use craft\db\ActiveRecord;

class FeedRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    public static function tableName()
    {
        return '{{%feedme_feeds}}';
    }
}
