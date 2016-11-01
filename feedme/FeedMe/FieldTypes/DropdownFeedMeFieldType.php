<?php
namespace Craft;

class DropdownFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = null;

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        // find matching option label
        foreach ($options as $option) {
            if ($data == $option['value']) {
                $fieldData = $option['value'];
                break;
            }
        }

        return $fieldData;
    }
    
}