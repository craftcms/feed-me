<?php

namespace semabit\feedme\records;

use craft\db\ActiveRecord;

class FeedRecord extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public static function tableName(): string
    {
        return '{{%feedme_feeds}}';
    }
}
