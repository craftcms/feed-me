<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/default';
    }



    // Public Methods
    // =========================================================================

    public function getFieldType()
    {
        return str_replace(array('Craft\\', 'FeedMeFieldType'), array('', ''), get_class($this));
    }

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');
        
        return $data;
    }
    
}