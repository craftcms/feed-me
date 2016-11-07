<?php
namespace Craft;

class CategoriesFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/categories';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();

        // Get source id's for connecting
        $source = $settings->getAttribute('source');
        list($type, $groupId) = explode(':', $source);

        // Find existing
        $categories = ArrayHelper::stringToArray($data);

        foreach ($categories as $category) {
            if ($category == '__') {
                continue;
            }

            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->groupId = $groupId;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            if (isset($options['options']['match'])) {
                $attribute = $options['options']['match'];
                $criteria->$attribute = DbHelper::escapeParam($category);
            } else {
                $criteria->title = DbHelper::escapeParam($category);
            }
            
            $elements = $criteria->ids();

            $fieldData = array_merge($fieldData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($options['options']['create'])) {
                    $fieldData[] = $this->_createElement($category, $groupId);
                }
            }
        }

        // Check for field limit - only return the specified amount
        if ($fieldData) {
            if ($field->settings['limit']) {
                $fieldData = array_chunk($fieldData, $field->settings['limit']);
                $fieldData = $fieldData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($options['fields'])) {
            $this->_populateElementFields($fieldData, $options['fields']);
        }

        return $fieldData;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($fieldData, $elementData)
    {
        foreach ($fieldData as $key => $id) {
            $category = craft()->categories->getCategoryById($id);

            // Prep each inner field
            $preppedElementData = array();
            foreach ($elementData as $elementHandle => $elementContent) {
                if ($elementContent != '__') {
                    $preppedElementData[$elementHandle] = craft()->feedMe_fields->prepForFieldType(null, $elementContent, $elementHandle, null);
                }
            }

            $category->setContentFromPost($preppedElementData);

            if (!craft()->categories->saveCategory($category)) {
                throw new Exception(json_encode($category->getErrors()));
            }
        }
    }

    private function _createElement($category, $groupId)
    {
        $element = new CategoryModel();
        $element->getContent()->title = $category;
        $element->groupId = $groupId;

        // Save category
        if (craft()->categories->saveCategory($element)) {
            return $element->id;
        } else {
            throw new Exception(json_encode($element->getErrors()));
        }
    }

}