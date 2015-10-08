<?php
namespace Craft;

class FeedMe_FeedModel extends BaseModel
{
    function __toString()
    {
        return Craft::t($this->name);
    }

    protected function defineAttributes()
    {
        return array(
            'id'                => AttributeType::Number,
            'name'              => AttributeType::String,
            'feedUrl'           => AttributeType::Url,
            'feedType'          => array(AttributeType::Enum, 'values' => array(
                FeedMe_FeedType::XML,
                FeedMe_FeedType::RSS,
                FeedMe_FeedType::ATOM,
                FeedMe_FeedType::JSON,
            )),
            'primaryElement'    => AttributeType::String,
            'section'           => AttributeType::String,
            'entrytype'         => AttributeType::String,
            'duplicateHandle'   => array(AttributeType::Enum, 'values' => array(
                FeedMe_Duplicate::Add,
                FeedMe_Duplicate::Update,
                FeedMe_Duplicate::Delete,
            )),
            'fieldMapping'      => AttributeType::Mixed,
            'fieldUnique'       => AttributeType::Mixed,
            'passkey'           => AttributeType::String,
            'backup'            => AttributeType::Bool,
        );
    }
}


  