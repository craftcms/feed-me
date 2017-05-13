<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class MatrixFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/matrix';
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

        // Store the fields for this Matrix - can't use the fields service due to context
        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');

        // Ensure when importing only one block thats its treated correctly.
        if (!isset($data[0])) {
            $data = array($data);
        }

        foreach ($data as $sortKey => $sortData) {
            $preppedFieldData = array();

            foreach ($sortData as $blockHandle => $blockFieldData) {
                foreach ($blockFieldData as $blockFieldHandle => $blockFieldContent) {

                    // Get the Matrix-contexted field for our regular field-prepping function
                    $blockType = $blockTypes[$blockHandle];

                    foreach ($blockType->getFields() as $f) {
                        if ($f->handle == $blockFieldHandle) {
                            $subField = $f;
                        }
                    }

                    if (!isset($subField)) {
                        continue;
                    }

                    $fieldOptions = array(
                        'field' => $subField,
                    );

                    // Special-case for table!
                    if ($subField->type == 'Table') {
                        $blockFieldContent = array('data' => $blockFieldContent);
                    }

                    // Parse this inner-field's data, just like a regular field
                    $parsedData = craft()->feedMe_fields->prepForFieldType(null, $blockFieldContent, $blockFieldHandle, $fieldOptions);

                    // Fire any post-processing for the field type
                    $posted = craft()->feedMe_fields->postForFieldType(null, $parsedData, $blockFieldHandle, $subField);

                    if ($posted) {
                        $parsedData = $parsedData[$blockFieldHandle];
                    }

                    if ($parsedData) {
                        $preppedFieldData[$blockFieldHandle] = $parsedData;
                    }
                }
            }

            $order = $sortKey + 1;

            $preppedData['new' . $order] = array(
                'type' => $blockHandle,
                'order' => $order,
                'enabled' => true,
                'fields' => $preppedFieldData,
            );
        }

        return $preppedData;
    }

    // Allows us to smartly-check to look at existing Matrix fields for an element, and whether data has changed or not.
    // No need to update Matrix blocks unless content has changed, which causes needless new elements to be created.
    public function checkExistingFieldData($element, $field, &$feedData, $handle)
    {
        $existingFieldData = array();
        $fieldData = Hash::get($feedData, $handle);

        // Get our Matrix blocks from the existing element
        $blocks = $element->getFieldValue($field->handle);

        foreach ($blocks as $key => $block) {
            $fieldValues = array();

            // Get all the inner fields for this Matrix block
            foreach ($block->getFieldLayout()->getFields() as $fieldLayoutField) {
                $innerField = $fieldLayoutField->getField();

                // Get the inner field content
                $fieldValue = $block->getFieldValue($innerField->handle);

                // If we have an Element Criteria Model (Entries, Assets, etc), get the ids
                if ($fieldValue instanceof ElementCriteriaModel) {
                    $fieldValue = $fieldValue->ids();
                }

                if ($fieldValue) {
                    $fieldValues[$innerField->handle] = $fieldValue;
                }
            }

            // Create an array of content so that it matches what we use to import - easy to compare this way
            $existingFieldData['new'.($key+1)] = array(
                'type' => $block->type->handle,
                'order' => $block->sortOrder,
                'enabled' => $block->type->enabled,
                'fields' => $fieldValues,
            );
        }

        // Now, we should have identically formatted existing content to how we're about to import.
        // Simply see if the arrays match exactly - size and attributes must be identical
        if ($existingFieldData == $fieldData) {
            // If they do equal, then nothing has changed from existing content. Se, we want to remove our mapped
            // data from the feed entirely, so the element doesn't get updated (because it doesn't need to),
            unset($feedData[$handle]);
        }
    }
    
}