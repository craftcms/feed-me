<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class DateFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');

        if ($data) {
            return DateTimeHelper::formatTimeForDb(DateTimeHelper::fromString($data, craft()->timezone));
        } else {
            return "";
        }
    }
    
}