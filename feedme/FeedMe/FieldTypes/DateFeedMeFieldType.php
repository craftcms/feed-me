<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class DateFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/date';
    }

    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $data = Hash::get($fieldData, 'data');
        $formatting = Hash::get($fieldData, 'options.match', 'auto');

        // Allow twig processing at this early stage
        if (strstr($data, '{{')) {
            $data = craft()->templates->renderObjectTemplate($data, $element);
        }

        $dateValue = FeedMeDateHelper::parseString($data, $formatting);

        if ($dateValue) {
            return DateTimeHelper::formatTimeForDb($dateValue);
        } else {
            return "";
        }
    }
    
}