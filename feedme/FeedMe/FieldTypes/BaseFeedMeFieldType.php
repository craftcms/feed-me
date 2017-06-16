<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/default';
    }



    // Public Methods
    // =========================================================================

    public function getFieldType()
    {
        return str_replace(array('Craft\\', 'FeedMeFieldType'), array('', ''), get_class($this));
    }

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');
        
        return $data;
    }

    public function postFieldData($element, $field, &$feedData, $handle)
    {
        if (!is_array($feedData)) {
            return;
        }

        // Parse all field content for Twig shorthand variables
        foreach ($feedData as $attribute => $data) {
            // Only check for string content at this stage
            if (!is_array($data)) {
                // Don't process the data unless we detect a Twig tag - also performance
                if (strpos($data, '{') !== false) {

                    $variable = preg_replace('/(?<![\{\%])\{(?![\{\%])/', '', $data);
                    $variable = preg_replace('/(?<![\}\%])\}(?![\}\%])/', '', $variable);

                    // Check that this element has an attribute or content for this provided variable
                    // But also allow traditional Twig - '{{ now }}' for instance
                    if (isset($element->$variable) || strstr($variable, '{{')) {
                        $feedData[$attribute] = craft()->templates->renderObjectTemplate($data, $element);
                    }
                }
            }
        }
    }

    public function checkExistingFieldData($element, $field, &$feedData, $handle)
    {
        // Check against existing and to-be-inserted content for each field. If it matches exactly
        // then we're wasting time updating the content. For performance, take it out of the elements'
        // field content.
        $fieldHandle = $field->handle;

        if (isset($element->content->$fieldHandle)) {
            $existingData = $element->content->$fieldHandle;
        } else {
            $existingData = null;
        }

        // Give the field type a chance to prep the existing data for use
        $fieldType = $field->getFieldType();

        if ($fieldType) {
            $fieldType->element = $element;
            $existingData = $fieldType->prepValue($existingData);
        }

        $fieldData = Hash::get($feedData, $handle);

        if ($existingData instanceof ElementCriteriaModel) {
            $existingData = $existingData->ids();
        }

        // Remove from the feed if there's a match of data
        if ($existingData == $fieldData) {
            unset($feedData[$handle]);
        }
    }
    
}