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

        $dateValue = FeedMeDateHelper::parseString($data);

        if ($dateValue) {
            return DateTimeHelper::formatTimeForDb($dateValue);
        } else {
            return "";
        }
    }
    
}