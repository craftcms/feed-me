<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

abstract class BaseFeedMeElementType
{
    // Public Methods
    // =========================================================================

    public function getElementType()
    {
        return str_replace(array('Craft\\', 'FeedMeElementType'), array('', ''), get_class($this));
    }

    // Protected Methods
    // =========================================================================

    protected function getObjectModel($data)
    {
        $objectModel = array();

        foreach (Hash::flatten($data) as $key => $value) {
            $filteredKey = str_replace('.data', '', $key);
            $objectModel[$filteredKey] = $value;
        }

        return Hash::expand($objectModel);
    }

    protected function prepareAuthorForElement($author)
    {
        if (!is_numeric($author)) {
            $criteria = craft()->elements->getCriteria(ElementType::User);
            $criteria->search = $author;
            $authorUser = $criteria->first();
            
            if ($authorUser) {
                $author = $authorUser->id;
            } else {
                $user = craft()->users->getUserByUsernameOrEmail($author);
                $author = $user ? $user->id : 1;
            }
        }

        return $author;
    }


    // Abstract Methods
    // =========================================================================

    abstract public function getGroups();
    
    abstract public function getGroupsTemplate();
    
    abstract public function getColumnTemplate();
    
    abstract public function getMappingTemplate();

    abstract public function setModel($settings);

    abstract public function setCriteria($settings);

    abstract public function matchExistingElement(&$criteria, $data, $settings);

    abstract public function delete(array $elements);

    abstract public function prepForElementModel(BaseElementModel $element, array &$data, $settings);

    abstract public function save(BaseElementModel &$element, array $data, $settings);

    abstract public function afterSave(BaseElementModel $element, array $data, $settings);
    
}