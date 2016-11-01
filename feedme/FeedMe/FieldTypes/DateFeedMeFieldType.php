<?php
namespace Craft;

class DateFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        return DateTimeHelper::formatTimeForDb(DateTimeHelper::fromString($data, craft()->timezone));
    }
    
}