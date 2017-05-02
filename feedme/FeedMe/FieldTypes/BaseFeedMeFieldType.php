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

    public function postFieldData($element, $field, &$fieldData, $handle)
    {
        if (!is_array($fieldData)) {
            return;
        }

        // Parse all field content for Twig shorthand variables
        foreach ($fieldData as $attribute => $data) {
            // Only check for string content at this stage
            if (!is_array($data)) {
                // Don't process the data unless we detect a Twig tag - also performance
                if (strpos($data, '{') !== false) {
                    $fieldData[$attribute] = craft()->templates->renderObjectTemplate($data, $element);
                }
            }
        }
    }
    
}