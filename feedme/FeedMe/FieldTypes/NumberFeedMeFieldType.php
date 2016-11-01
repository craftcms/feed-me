<?php
namespace Craft;

class NumberFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        return floatval(LocalizationHelper::normalizeNumber($data));
    }
    
}