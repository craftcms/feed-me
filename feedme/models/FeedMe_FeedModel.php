<?php
namespace Craft;

class FeedMe_FeedModel extends BaseModel
{
    public function __toString()
    {
        return Craft::t($this->name);
    }

    /*public function getElementGroupForType()
    {
        if ($this->elementType) {
            if (isset($this->elementGroup[$this->elementType])) {
                $group = $this->elementGroup[$this->elementType];

                return craft()->sections->getSectionById($group);
            }
        }
    }

    public function getElementTypeForFeed()
    {
        //if ($this->elementType) {
            //return craft()->elements->getElementType($this->elementType);
        //}
    }

    public function getSection()
    {
        if ($this->elementType == 'Entry') {
            $section = $this->_getSectionEntryType();
            return craft()->sections->getSectionById($section['section']);
        }
    }

    public function getEntryType()
    {
        if ($this->elementType == 'Entry') {
            $entryType = $this->_getSectionEntryType();
            return craft()->sections->getEntryTypeById($entryType['entryType']);
        }
    }

    public function getCategory()
    {
        if ($this->elementType == 'Category') {
            return craft()->categories->getCategoryById($this->elementGroup['Category']);
        }
    }

    public function getUser()
    {
        if ($this->elementType == 'User') {
            return craft()->users->getUserById($this->elementGroup['User']);
        }
    }

    public function getCommerceProduct()
    {
        if ($this->elementType == 'Commerce_Product') {
            return craft()->commerce->getProductById($this->elementGroup['Commerce_Product']);
        }
    }

    private function _getSectionEntryType()
    {
        if ($this->elementType == 'Entry') {
            $sectionEntryType = explode(':', $this->elementGroup['Entry']);
            return array('section' => $sectionEntryType[0], 'entryType' => $sectionEntryType[1]);
        }
    }*/

    protected function defineAttributes()
    {
        return array(
            'id'                => AttributeType::Number,
            'name'              => AttributeType::String,
            'feedUrl'           => AttributeType::Uri,
            'feedType'          => array(AttributeType::Enum, 'values' => array(
                FeedMe_FeedType::XML,
                FeedMe_FeedType::RSS,
                FeedMe_FeedType::ATOM,
                FeedMe_FeedType::JSON,
            )),
            'primaryElement'    => AttributeType::String,
            'elementType'       => AttributeType::String,
            'elementGroup'      => AttributeType::Mixed,
            'locale'            => AttributeType::String,
            'duplicateHandle'   => array(AttributeType::Enum, 'values' => array(
                FeedMe_Duplicate::Add,
                FeedMe_Duplicate::Update,
                FeedMe_Duplicate::Delete,
            )),
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


  