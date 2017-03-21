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
            'feedType'          => array(AttributeType::String, 'required' => true),
            'primaryElement'    => array(AttributeType::String),
            'elementType'       => array(AttributeType::String, 'required' => true),
            'elementGroup'      => array(AttributeType::Mixed),
            'locale'            => array(AttributeType::String),
            'duplicateHandle'   => array(AttributeType::Mixed, 'required' => true),
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



