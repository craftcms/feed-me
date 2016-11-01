<?php
namespace Craft;

class FeedMe_FeedRecord extends BaseRecord
{
    public function getTableName()
    {
        return 'feedme_feeds';
    }

    protected function defineAttributes()
    {
        return array(
            'name'              => array(AttributeType::String, 'required' => true),
            'feedUrl'           => array(AttributeType::Uri, 'required' => true, 'column' => ColumnType::Text),
            'feedType'          => array(AttributeType::Enum, 'required' => true, 'values' => array(
                FeedMe_FeedType::XML,
                FeedMe_FeedType::RSS,
                FeedMe_FeedType::ATOM,
                FeedMe_FeedType::JSON,
            )),
            'primaryElement'    => array(AttributeType::String),
            'elementType'       => array(AttributeType::String, 'required' => true),
            'elementGroup'      => array(AttributeType::Mixed),
            'locale'            => array(AttributeType::String),
            'duplicateHandle'   => array(AttributeType::Enum, 'required' => true, 'values' => array(
                FeedMe_Duplicate::Add,
                FeedMe_Duplicate::Update,
                FeedMe_Duplicate::Delete,
            )),
            'fieldMapping'          => AttributeType::Mixed,
            'fieldDefaults'         => AttributeType::Mixed,
            'fieldElementMapping'   => AttributeType::Mixed,
            'fieldElementDefaults'  => AttributeType::Mixed,
            'fieldUnique'           => AttributeType::Mixed,
            'passkey'               => array(AttributeType::String, 'required' => true),
            'backup'                => AttributeType::Bool,
        );
    }

    public function scopes()
    {
        return array(
            'ordered' => array('order' => 'name'),
        );
    }
}



