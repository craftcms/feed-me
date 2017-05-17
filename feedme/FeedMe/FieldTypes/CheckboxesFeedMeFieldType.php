<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class CheckboxesFeedMeFieldType extends BaseFeedMeFieldType
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

        $attribute = Hash::get($fieldData, 'options.match', 'value');

        foreach ($options as $option) {
            foreach ($data as $dataValue) {
                if ($dataValue == $option[$attribute]) {
                    $preppedData[] = $option['value'];
                }
            }
        }

        return $preppedData;
    }
    
}