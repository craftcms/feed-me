<?php
namespace Craft;

class FeedMe_LogsRecord extends BaseRecord
{

    public function getTableName()
    {
        return 'feedme_logs';
    }

    protected function defineAttributes()
    {
        return array(
            'items'     => AttributeType::Number,
        );
    }

    public function defineRelations()
    {
        return array(
            'feed' => array(static::BELONGS_TO, 'FeedMe_FeedRecord', 'onDelete' => static::CASCADE, 'required' => false),
            'log'  => array(static::HAS_MANY,   'FeedMe_LogRecord', 'logId'),
        );
    }
}
