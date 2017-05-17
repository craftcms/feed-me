<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class RadioButtonsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/option-select';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = null;

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return;
        }

        $settings = $field->getFieldType()->getSettings();
        $options = $settings->getAttribute('options');

        $attribute = Hash::get($fieldData, 'options.match', 'value');

        foreach ($options as $option) {
            if ($data == $option[$attribute]) {
                $preppedData = $option['value'];
                break;
            }
        }

        return $preppedData;
    }
    
}