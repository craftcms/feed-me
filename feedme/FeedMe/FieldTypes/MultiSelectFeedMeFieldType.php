<?php
namespace Craft;

class MultiSelectFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        return ArrayHelper::stringToArray($data);
    }
    
}