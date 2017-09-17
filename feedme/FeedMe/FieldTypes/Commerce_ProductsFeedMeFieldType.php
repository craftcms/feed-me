<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class Commerce_ProductsFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/commerce_products';
    }
    

    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $fieldData, $handle, $options)
    {
        $preppedData = array();

        $data = Hash::get($fieldData, 'data');

        if (empty($data)) {
            return array();
        }

        if (!is_array($data)) {
            $data = array($data);
        }

        $settings = $field->getFieldType()->getSettings();

        $typeIds = array();
        $types = $settings->getAttribute('sources');

        if (is_array($types)) {
            foreach ($types as $type) {
                list(, $id) = explode(':', $type);
            }
        }

        // Find existing
        foreach ($data as $product) {
            $criteria = craft()->elements->getCriteria('Commerce_Product');
            $criteria->status = null;
            $criteria->typeId = $typeIds;
            $criteria->limit = $settings->limit;

            // Check if we've specified which attribute we're trying to match against
            $attribute = Hash::get($fieldData, 'options.match', 'title');
            $criteria->$attribute = DbHelper::escapeParam($product);
            $elements = $criteria->ids();
            
            $preppedData = array_merge($preppedData, $elements);
        }

        // Check for field limit - only return the specified amount
        if ($preppedData) {
            if ($field->settings['limit']) {
                $preppedData = array_chunk($preppedData, $field->settings['limit']);
                $preppedData = $preppedData[0];
            }
        }

        // Check if we've got any data for the fields in this element
        if (isset($fieldData['fields'])) {
            $this->_populateElementFields($preppedData, $fieldData['fields']);
        }

        return $preppedData;
    }

}