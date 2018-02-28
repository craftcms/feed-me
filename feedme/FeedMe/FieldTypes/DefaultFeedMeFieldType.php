<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

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

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');

        // The default field handler is for simple field content, ditch array content.
        if (is_array($data)) {
            return;
        }

        return $data;
    }
    
}