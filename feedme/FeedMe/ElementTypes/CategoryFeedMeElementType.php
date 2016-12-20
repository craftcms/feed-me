<?php
namespace Craft;

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
            if (intval($value) == 1 && ($data != '__')) {
                if (isset($data[$handle])) {
                    $criteria->$handle = DbHelper::escapeParam($data[$handle]);
                } else {
                    throw new Exception(Craft::t('Unable to match against '.$handle.' - no data found.'));
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
    
    public function prepForElementModel(BaseElementModel $element, array &$data, $settings, $options)
    {
        if (isset($settings['locale'])) {
            $element->localeEnabled = true;
        }

        foreach ($data as $handle => $value) {
            if ($value == '' || $value == '__') {
                continue;
            }

            switch ($handle) {
                case 'id';
                    $element->$handle = $value;
                    break;
                case 'slug':
                    $element->$handle = ElementHelper::createSlug($value);
                    break;
                case 'title':
                    $element->getContent()->$handle = $value;
                    break;
                default:
                    continue 2;
            }

            // Update the original data in our feed - for clarity in debugging
            $data[$handle] = $element->$handle;
        }

        return $element;
    }

    public function save(BaseElementModel &$element, $settings)
    {
        return craft()->categories->saveCategory($element);
    }

    public function afterSave(BaseElementModel $element, array $data, $settings)
    {
        $parentCategory = null;

        if (isset($data['parent'])) {
            $parentCategory = $this->_prepareParentForElement($data['parent'], $element->groupId);
        } elseif (isset($data['ancestors'])) {
            $parentCategory = $this->_prepareAncestorsForElement($element, $data['ancestors']);
        }

        if ($parentCategory) {
            $categoryGroup = craft()->categories->getGroupById($element->groupId);
            craft()->structures->append($categoryGroup->structureId, $element, $parentCategory, 'auto');
        }
    }



    // Private Methods
    // =========================================================================

    private function _prepareParentForElement($data, $groupId)
    {
        $parentCategory = null;

        // Don't connect empty fields
        if (!empty($data)) {

            // Find matching element
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->groupId = $groupId;

            // Exact match
            $criteria->search = '"'.$data.'"';
            $parentCategory = $criteria->first();
        }

        return $parentCategory;
    }

    private function _prepareAncestorsForElement(BaseElementModel $element, $data)
    {
        $parentCategory = null;

        // Don't connect empty fields
        if (!empty($data)) {

            // This we append before the slugified path
            $categoryUrl = str_replace('{slug}', '', $element->getUrlFormat());

            // Find matching element by URI (dirty, not all categories have URI's)
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->groupId = $element->groupId;
            $criteria->uri = $categoryUrl . craft()->import->slugify($data);
            $criteria->limit = 1;

            $parentCategory = $criteria->first();
        }

        return $parentCategory;
    }
}