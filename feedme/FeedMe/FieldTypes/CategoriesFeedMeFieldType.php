<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

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

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = array();

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return array();
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        $settings = $field->getFieldType()->getSettings();

        // Get source id's for connecting
        $source = $settings->getAttribute('source');
        list($type, $groupId) = explode(':', $source);

        // Find existing
        foreach ($data as $category) {
            $criteria = craft()->elements->getCriteria(ElementType::Category);
            $criteria->status = null;
            $criteria->groupId = $groupId;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            $attribute = Hash::get($fieldData, 'options.match', 'title');
            $criteria->$attribute = DbHelper::escapeParam($category);
            $elements = $criteria->ids();

            $preppedData = array_merge($preppedData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($fieldData['options']['create'])) {
                    $preppedData[] = $this->_createElement($category, $groupId);
                }
            }
        }

        // Check for field limit - only return the specified amount
        if ($preppedData) {
            if ($field->settings['limit']) {
                $preppedData = array_chunk($preppedData, $field->settings['limit']);
                $preppedData = $preppedData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($fieldData['fields'])) {
            $this->_populateElementFields($preppedData, $fieldData['fields']);
        }

        return $preppedData;
    }



    // Private Methods
    // =========================================================================

    private function _populateElementFields($categoryData, $fieldData)
    {
        foreach ($categoryData as $i => $categoryId) {
            $category = craft()->categories->getCategoryById($categoryId);

            // Prep each inner field
            $preppedData = array();
            foreach ($fieldData as $fieldHandle => $fieldContent) {
                $data = craft()->feedMe_fields->prepForFieldType(null, $fieldContent, $fieldHandle, null);

                if (is_array($data)) {
                    $data = Hash::get($data, $i);
                }

                $preppedData[$fieldHandle] = $data;

                if (craft()->config->get('checkExistingFieldData', 'feedMe')) {
                    $field = craft()->fields->getFieldByHandle($fieldHandle);

                    craft()->feedMe_fields->checkExistingFieldData($category, $preppedData, $fieldHandle, $field);
                }
            }

            if ($preppedData) {
                $category->setContentFromPost($preppedData);

                if (!craft()->categories->saveCategory($category)) {
                    FeedMePlugin::log('Category error: ' . json_encode($category->getErrors()), LogLevel::Error, true);
                } else {
                    FeedMePlugin::log('Updated Category (ID ' . $categoryId . ') inner-element with content: ' . json_encode($preppedData), LogLevel::Info, true);
                }
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