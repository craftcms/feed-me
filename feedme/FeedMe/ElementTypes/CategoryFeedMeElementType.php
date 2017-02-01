<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class CategoryFeedMeElementType extends BaseFeedMeElementType
{
    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feedme/_includes/elements/category/groups';
    }

    public function getColumnTemplate()
    {
        return 'feedme/_includes/elements/category/column';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return craft()->categories->getEditableGroups();
    }

    public function setModel($settings)
    {
        // Set up new category model
        $element = new CategoryModel();
        $element->groupId = $settings['elementGroup']['Category'];

        if ($settings['locale']) {
            $element->locale = $settings['locale'];
        }

        return $element;
    }

    public function setCriteria($settings)
    {
        // Match with current data
        $criteria = craft()->elements->getCriteria(ElementType::Category);
        $criteria->status = null;
        $criteria->limit = null;
        $criteria->localeEnabled = null;
        
        $criteria->groupId = $settings['elementGroup']['Category'];

        return $criteria;
    }

    public function matchExistingElement(&$criteria, $data, $settings)
    {
        foreach ($settings['fieldUnique'] as $handle => $value) {
            if ((int)$value === 1) {
                $feedValue = Hash::get($data, $handle . '.data', $data[$handle]);

                if ($feedValue) {
                    $criteria->$handle = DbHelper::escapeParam($feedValue);
                }
            }
        }

        // Check to see if an element already exists - interestingly, find()[0] is faster than first()
        return $criteria->find();
    }

    public function delete(array $elements)
    {
        return craft()->categories->deleteCategory($elements);
    }
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings)
    {
        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            switch ($handle) {
                case 'id';
                    $element->$handle = $value['data'];
                    break;
                case 'slug':
                    $element->$handle = ElementHelper::createSlug($value['data']);
                    break;
                //case 'parent':
                    //$element->parent = $this->_findParent($value);
                    //break;
                case 'title':
                    $element->getContent()->$handle = $value['data'];
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, array $data, $settings)
    {
        // Are we targeting a specific locale here? If so, we create an essentially blank element
        // for the primary locale, and instead create a locale for the targeted locale
        if (isset($settings['locale'])) {
            // Save the default locale element empty
            if (craft()->categories->saveCategory($element)) {
                // Now get the successfully saved (empty) element, and set content on that instead
                $elementLocale = craft()->categories->getCategoryById($element->id, $settings['locale']);
                $elementLocale->setContentFromPost($data);

                // Save the locale entry
                return craft()->categories->saveCategory($elementLocale);
            } else {
                if ($element->getErrors()) {
                    throw new Exception(json_encode($element->getErrors()));
                } else {
                    throw new Exception(Craft::t('Unknown Element error occurred.'));
                }
            }

            return false;
        } else {
            return craft()->categories->saveCategory($element);
        }
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        $parentCategory = null;

        if (isset($data['parent'])) {
            $parentCategory = $this->_prepareParentForElement($data['parent'], $element->groupId);
        }

        if ($parentCategory) {
            $categoryGroup = craft()->categories->getGroupById($element->groupId);
            craft()->structures->append($categoryGroup->structureId, $element, $parentCategory, 'auto');
        }
    }



    // Private Methods
    // =========================================================================

    private function _prepareParentForElement($fieldData, $groupId)
    {
        $parentCategory = null;

        $data = Hash::get($fieldData, 'data');
        $attribute = Hash::get($fieldData, 'options.match', 'id');

        if (!empty($data)) {
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->groupId = $groupId;
            $criteria->$attribute = DbHelper::escapeParam($data);
            $criteria->limit = 1;
            $parentCategory = $criteria->first();
        }

        return $parentCategory;
    }
}
