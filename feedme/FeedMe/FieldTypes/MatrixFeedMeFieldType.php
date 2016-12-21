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
        $sortedData = array();

        // Because of how mapping works, we need to do some extra work here, which is a pain!
        // This is to ensure blocks are ordered as they are provided. Data will be provided as:
        // blockhandle = [
        //   fieldHandle = [
        //     orderIndex = [
        //       data
        //     ]
        //   ]
        // ]
        //
        // We change it to:
        //
        // orderIndex = [
        //   blockhandle = [
        //     orderIndex = [
        //       data
        //     ]
        //   ]
        // ]
        foreach ($data as $blockHandle => $block) {
            foreach ($block as $blockFieldHandle => $blockFieldData) {
                // When we import a non-repeatable node into a Matrix, we must ensure its treated consistently
                // Because Matrix/MatrixItem/Node is not the same as Matrix/MatrixItem/.../Node - it should be the latter
                // This is why XML sucks - JSON just wouldn't have this issue...
                if (isset($options['feedHandle'])) {
                    if (!strstr($options['feedHandle'][0], '/.../')) {
                        $blockFieldData = array($blockFieldData);
                    }
                }

                if ($blockFieldData == '__') {
                    continue;
                }

                if (!is_array($blockFieldData)) {
                    $blockFieldData = array($blockFieldData);
                }

                foreach ($blockFieldData as $blockOrder => $innerData) {
                    $sortedData[$blockOrder][$blockHandle][$blockFieldHandle] = $innerData;
                }
            }
        }

        // Sort by the new ordering we've set
        ksort($sortedData);

        // Store the fields for this Matrix - can't use the fields service due to context
        $blockTypes = craft()->matrix->getBlockTypesByFieldId($field->id, 'handle');

        $count = 0;
        $allPreppedFieldData = array();

        foreach ($sortedData as $sortKey => $sortData) {
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

                    // Get any options for the field - stored a little different
                    $fieldOptions = array();
                    if (isset($options['options'][$blockHandle][$blockFieldHandle])) {
                        $fieldOptions['options'] = $options['options'][$blockHandle][$blockFieldHandle];
                    }

                    if (isset($options['fields'][$blockHandle][$blockFieldHandle])) {
                        $fieldOptions['fields'] = $options['fields'][$blockHandle][$blockFieldHandle];
                    }

                    $fieldOptions['feedHandle'] = $options['feedHandle'];
                    $fieldOptions['field'] = $subField;
                    $fieldOptions['parentField'] = $field;

                    // Parse this inner-field's data, just like a regular field
                    $parsedData = craft()->feedMe_fields->prepForFieldType($element, $blockFieldContent, $blockFieldHandle, $fieldOptions);
                    
                    if ($parsedData) {
                        // Special-case for inner table - not a great solution at the moment, needs to be more flexible
                        if ($subField->type == 'Table') {
                            foreach ($parsedData as $i => $tableFieldRow) {
                                $next = reset($tableFieldRow);

                                if (!is_array($next)) {
                                    $tableFieldRow = array($i => $tableFieldRow);
                                }

                                foreach ($tableFieldRow as $j => $tableFieldColumns) {
                                    foreach ($tableFieldColumns as $k => $tableFieldColumn) {
                                        $allPreppedFieldData[$k][$blockHandle][$blockFieldHandle][$j][$sortKey] = $tableFieldColumn;
                                    }
                                }
                            }
                        } else {
                            $allPreppedFieldData[$sortKey][$blockHandle][$blockFieldHandle] = $parsedData;
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

                foreach ($preppedBlockFieldData as $blockHandle => $preppedFieldData) {

                    // But check do we even have any field data to add?
                    if ($this->_checkForFieldData($preppedFieldData)) {
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


    private function _checkForFieldData($fieldData)
    {
        // Check if we have any field data. Important to check for field options for elements. These
        // will be included - wrongly showing we have data to import.
        $validData = 0;

        foreach ($fieldData as $key => $data) {
            if ($data != '__') {
                $validData++;
            }
        }

        return (bool)$validData;
    }
    
}