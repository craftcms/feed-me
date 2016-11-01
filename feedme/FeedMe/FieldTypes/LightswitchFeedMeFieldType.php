<?php
namespace Craft;

class LightswitchFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        if ($data == Craft::t('Yes')) {
            return true;
        } else {
            return false;
        }
    }
    
}