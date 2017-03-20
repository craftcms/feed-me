<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class LightswitchFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');

        if ($data == Craft::t('Yes') || (bool)$data) {
            return true;
        } else {
            return false;
        }
    }
    
}