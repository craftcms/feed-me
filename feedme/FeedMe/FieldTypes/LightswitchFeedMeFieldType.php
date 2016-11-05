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
        if ($data == Craft::t('Yes') || $data == 'true' || $data == '1') {
            return true;
        } else {
            return false;
        }
    }
    
}