<?php
namespace Craft;

class TagsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================


    


    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        if (!empty($data)) {
            $settings = $field->getFieldType()->getSettings();

            // Get tag group id
            $source = $settings->getAttribute('source');
            list($type, $groupId) = explode(':', $source);

            // Sanitize
            $data = DbHelper::escapeParam($data);

            // Find existing tag
            $criteria = craft()->elements->getCriteria(ElementType::Tag);
            $criteria->groupId = $groupId;
            $criteria->title = $data;

            if (!$criteria->total()) {
                // Create tag if one doesn't already exist
                $newtag = new TagModel();
                $newtag->getContent()->title = $data;
                $newtag->groupId = $groupId;

                // Save tag
                if (craft()->tags->saveTag($newtag)) {
                    $fieldData = array($newtag->id);
                }
            } else {
                $fieldData = $criteria->ids();
            }
        }

        return $fieldData;
    }
    
}