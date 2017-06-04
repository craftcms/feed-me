<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class TagsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/tags';
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

        // Get tag group id
        $source = $settings->getAttribute('source');
        list($type, $groupId) = explode(':', $source);

        // Find existing
        foreach ($data as $tag) {
            $criteria = craft()->elements->getCriteria(ElementType::Tag);
            $criteria->status = null;
            $criteria->groupId = $groupId;
            $criteria->title = DbHelper::escapeParam($tag);
            
            $elements = $criteria->ids();

            $preppedData = array_merge($preppedData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($fieldData['options']['create'])) {
                    $preppedData[] = $this->_createElement($tag, $groupId);
                }
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

    private function _populateElementFields($tagData, $fieldData)
    {
        foreach ($tagData as $i => $tagId) {
            $tag = craft()->tags->getTagById($tagId, null);

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

                    craft()->feedMe_fields->checkExistingFieldData($tag, $preppedData, $fieldHandle, $field);
                }
            }

            if ($preppedData) {
                $tag->setContentFromPost($preppedData);

                if (!craft()->tags->saveTag($tag)) {
                    FeedMePlugin::log('Tag error: ' . json_encode($tag->getErrors()), LogLevel::Error, true);
                } else {
                    FeedMePlugin::log('Updated Tag (ID ' . $tagId . ') inner-element with content: ' . json_encode($preppedData), LogLevel::Info, true);
                }
            }
        }
    }

    private function _createElement($tag, $groupId)
    {
        $element = new TagModel();
        $element->getContent()->title = $tag;
        $element->groupId = $groupId;

        // Save tag
        if (craft()->tags->saveTag($element)) {
            return $element->id;
        } else {
            throw new Exception(json_encode($element->getErrors()));
        }
    }

}