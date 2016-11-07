<?php
namespace Craft;

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

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();

        // Get tag group id
        $source = $settings->getAttribute('source');
        list($type, $groupId) = explode(':', $source);

        // Find existing
        $tags = ArrayHelper::stringToArray($data);

        foreach ($tags as $tag) {
            if ($tag == '__') {
                continue;
            }

            $criteria = craft()->elements->getCriteria(ElementType::Tag);
            $criteria->groupId = $groupId;
            $criteria->title = DbHelper::escapeParam($tag);
            
            $elements = $criteria->ids();

            $fieldData = array_merge($fieldData, $elements);

            // Create the elements if we require
            if (count($elements) == 0) {
                if (isset($options['options']['create'])) {
                    $fieldData[] = $this->_createElement($tag, $groupId);
                }
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
            $tag = craft()->tags->getTagById($id, null);

            // Prep each inner field
            $preppedElementData = array();
            foreach ($elementData as $elementHandle => $elementContent) {
                if ($elementContent != '__') {
                    $preppedElementData[$elementHandle] = craft()->feedMe_fields->prepForFieldType(null, $elementContent, $elementHandle, null);
                }
            }

            $tag->setContentFromPost($preppedElementData);

            if (!craft()->tags->saveTag($tag)) {
                throw new Exception(json_encode($tag->getErrors()));
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