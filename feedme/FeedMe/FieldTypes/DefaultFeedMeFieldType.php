<?php
namespace Craft;

class DefaultFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/default';
    }
    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        return $data;
    }
    
}