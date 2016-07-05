<?php
namespace Craft;

class FeedMe_LogRecord extends BaseRecord
{

    public function getTableName()
    {
        return 'feedme_log';
    }

    protected function defineAttributes()
    {
        return array(
            'errors'   => AttributeType::Mixed,
        );
    }

    public function defineRelations()
    {
        return array(
            'logs'  => array(static::BELONGS_TO, 'FeedMe_LogsRecord'),
        );
    }
}
