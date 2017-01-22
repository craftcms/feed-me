<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class DropdownFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = array();

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        // find matching option label
        foreach ($options as $option) {
            if ($data == $option['value']) {
                $preppedData = $option['value'];
                break;
            }
        }

        return $preppedData;
    }
    
}