<?php
namespace Craft;

class FeedMe_FeedModel extends BaseModel
{
    // Public Methods
    // =========================================================================

    public function __toString()
    {
        return Craft::t($this->name);
    }

    public function getDuplicateHandleFriendly()
    {
        return FeedMeDuplicate::getFrieldly($this->duplicateHandle);
    }

    public function directUrl()
    {
        $directUrl = UrlHelper::getActionUrl('/feedMe/feeds/runTask', array(
            'direct' => true,
            'feedId' => $this->id,
            'passkey' => $this->passkey,
        ));

        $directUrl = str_replace(craft()->config->get('cpTrigger') . '/', '', $directUrl);

        return $directUrl;
    }

    // Protected Methods
    // =========================================================================

    protected function defineAttributes()
    {
        return array(
            'id'                => AttributeType::Number,
            'name'              => AttributeType::String,
            'feedUrl'           => AttributeType::Uri,
            'feedType'          => AttributeType::String,
            'primaryElement'    => AttributeType::String,
            'elementType'       => AttributeType::String,
            'elementGroup'      => AttributeType::Mixed,
            'locale'            => AttributeType::String,
            'duplicateHandle'   => AttributeType::Mixed,
            'fieldMapping'          => AttributeType::Mixed,
            'fieldDefaults'         => AttributeType::Mixed,
            'fieldElementMapping'   => AttributeType::Mixed,
            'fieldElementDefaults'  => AttributeType::Mixed,
            'fieldUnique'           => AttributeType::Mixed,
            'passkey'               => AttributeType::String,
            'backup'                => AttributeType::Bool,
        );
    }
}


  