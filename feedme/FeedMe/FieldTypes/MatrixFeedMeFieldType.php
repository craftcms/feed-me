<?php
namespace Craft;

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

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        // When we import a non-repeatable node into a Matrix, we must ensure its treated consistently
        // Because Matrix/MatrixItem/Node is not the same as Matrix/MatrixItem/.../Node - it should be the latter
        if (!strstr($options['feedHandle'][0], '/.../')) {
            foreach ($data as $blockHandle => $block) {
                foreach ($block as $blockFieldHandle => $blockFieldData) {
                    $data[$blockHandle][$blockFieldHandle] = array($blockFieldData);
                }
            }
        }

        // Store the fields for this Matrix - can't use the fields service due to context
        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');

        $count = 0;
        foreach ($data as $blockHandle => $block) {
            $allPreppedFieldData = array();

            // Do some pre-processing first, due to the way feed-mapping works, each data will be grouped across multiple blocks
            // by its inner handle, which isn't really what we want. Instead, loop through each inner field,
            // and ensure its stored on the appropriate outer block.
            foreach ($block as $blockFieldHandle => $blockFieldData) {
                if (!is_array($blockFieldData) || !isset($blockFieldData[0])) {
                    $blockFieldData = array($blockFieldData);
                }

                foreach ($blockFieldData as $key => $blockFieldContent) {
                    if ($blockFieldContent) {

                        // Get the Matrix-contexted field for our regular field-prepping function
                        $blockType = $blockTypes[$blockHandle];

                        foreach ($blockType->getFields() as $f) {
                            if ($f->handle == $blockFieldHandle) {
                                $subField = $f;
                            }
                        }

                        $options['field'] = $subField;
                        $options['parentField'] = $field;

                        // Parse this inner-field's data, just like a regular field
                        $parsedData = craft()->feedMe_fields->prepForFieldType($element, $blockFieldContent, $blockFieldHandle, $options);

                        if ($parsedData) {
                            // Special-case for inner table - not a great solution at the moment, needs to be more flexible
                            if (substr_count($options['feedHandle'][0], '/.../') == 2) {
                                foreach ($parsedData as $i => $tableFieldRow) {
                                    $next = reset($tableFieldRow);

                                    if (!is_array($next)) {
                                        $tableFieldRow = array($i => $tableFieldRow);
                                    }

                                    foreach ($tableFieldRow as $j => $tableFieldColumn) {
                                        $allPreppedFieldData[$j][$blockHandle][$blockFieldHandle][$i] = $tableFieldColumn;
                                    }
                                }
                            } else {
                                $allPreppedFieldData[$blockHandle][$key][$blockFieldHandle] = $parsedData;
                            }
                        }
                    }
                }
            }

            // Now we've got a bit more sane data - its a simple (regular) import
            if ($allPreppedFieldData) {
                foreach ($allPreppedFieldData as $key => $preppedBlockFieldData) {

                    // Sort by keys - otherwise can potentially have issues with ordering
                    ksort($preppedBlockFieldData);

                    foreach ($preppedBlockFieldData as $preppedFieldData) {
                        $fieldData['new'.($count+1)] = array(
                            'type' => $blockHandle,
                            'order' => ($count+1),
                            'enabled' => true,
                            'fields' => $preppedFieldData,
                        );

                        $count++;
                    }
                }
            }
        }

        return $fieldData;
    }

    // Allows us to smartly-check to look at existing Matrix fields for an element, and whether data has changed or not.
    // No need to update Matrix blocks unless content has changed, which causes needless new elements to be created.
    public function postFieldData($element, $field, &$data, $handle)
    {
        $existingFieldData = array();
        $fieldData = $data[$handle];

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
            unset($data[$handle]);
        }
    }
    
}