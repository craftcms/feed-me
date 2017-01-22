<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class NumberFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');

        return floatval(LocalizationHelper::normalizeNumber($data));
    }
    
}