<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class CheckboxesFeedMeFieldType extends BaseFeedMeFieldType
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
            return array();
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        foreach ($options as $option) {
            foreach ($data as $dataValue) {
                if ($dataValue == $option['value']) {
                    $preppedData[] = $option['value'];
                }
            }
        }

        return $preppedData;
    }
    
}