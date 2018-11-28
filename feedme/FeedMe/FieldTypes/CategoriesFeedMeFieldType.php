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
                    $preppedData[] = $this->_createElement($category, $groupId, $fieldData);
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

                if (craft()->config->get('checkExistingFieldData', 'feedme')) {
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

    private function _createElement($category, $groupId, $fieldData)
    {
        $content = [];
        $error = null;

        $element = new CategoryModel();
        $element->getContent()->title = $category;
        $element->groupId = $groupId;

        // Save category
        if (craft()->categories->saveCategory($element)) {
            return $element->id;
        } else {
            $error = true;
        }

        // Its important to check if we have any required fields on the element we're trying to create
        // if we do, we need to populate those. This is only ever checked if there's an error when saving the
        // element normally, which just saves us having to do a query each run through for non-required groups
        if ($error) {
            $group = craft()->categories->getGroupById($groupId);

            $requiredFields = craft()->db->createCommand()
                ->select('f.id, f.handle')
                ->from('fieldlayoutfields flf')
                ->join('fields f', 'flf.fieldId = f.id')
                ->where('flf.layoutId = :layoutId', array(':layoutId' => $group->fieldLayoutId))
                ->andWhere('flf.required = 1')
                ->queryAll();

            if ($requiredFields) {
                foreach ($requiredFields as $requiredField) {
                    $handle = $requiredField['handle'];

                    // Get the content for this inner field from our overall feed data
                    $innerFieldContent = Hash::get($fieldData, 'fields.' . $handle);

                    // Parse this inner-field's data, just like a regular field
                    $parsedData = craft()->feedMe_fields->prepForFieldType(null, $innerFieldContent, $handle, null);

                    // Set the required content on the element
                    $element->setContentFromPost(array($handle => $parsedData));
                }
            }

            // Try to save the element again - it failed above
            // // Save category
            if (craft()->categories->saveCategory($element)) {
                return $element->id;
            } else {
                throw new Exception(json_encode($element->getErrors()));
            }
        }
    }

}