<?php
namespace Craft;

abstract class BaseFeedMeElementType
{
    // Public Methods
    // =========================================================================

    public function getElementType()
    {
        return str_replace(array('Craft\\', 'FeedMeElementType'), array('', ''), get_class($this));
    }


    // Abstract Methods
    // =========================================================================

    abstract public function getGroups();
    
    abstract public function getGroupsTemplate();
    
    abstract public function getColumnTemplate();

    abstract public function setModel($settings);

    abstract public function setCriteria($settings);

    abstract public function matchExistingElement(&$criteria, $data, $settings);

    abstract public function delete(array $elements);

    abstract public function prepForElementModel(BaseElementModel $element, array &$data, $settings, $options);

    abstract public function save(BaseElementModel &$element, $settings);

    abstract public function afterSave(BaseElementModel $element, array $data, $settings);
    
}