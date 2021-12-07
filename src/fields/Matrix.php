<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\Plugin;

/**
 *
 * @property-read string $mappingTemplate
 */
class Matrix extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Matrix';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Matrix';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/matrix';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField()
    {
        $preppedData = [];
        $fieldData = [];
        $complexFields = [];

        $blocks = Hash::get($this->fieldInfo, 'blocks');

        // Before we do anything, we need to extract the data from our feed and normalise it. This is especially
        // complex due to sub-fields, which each can be a variety of fields and formats, compounded by multiple or
        // Matrix blocks - we don't know! We also need to be careful of the order data is in the feed to be
        // reflected in the field - phew!
        //
        // So, in order to keep data in the order provided in our feed, we start there (as opposed to looping through blocks)

        foreach ($this->feedData as $nodePath => $value) {
            // Get the field mapping info for this node in the feed
            $fieldInfo = $this->_getFieldMappingInfoForNodePath($nodePath, $blocks);

            // If this is data concerning our Matrix field and blocks
            if ($fieldInfo) {
                $blockHandle = $fieldInfo['blockHandle'];
                $subFieldHandle = $fieldInfo['subFieldHandle'];
                $subFieldInfo = $fieldInfo['subFieldInfo'];
                $isComplexField = $fieldInfo['isComplexField'];

                $nodePathSegments = explode('/', $nodePath);
                $blockIndex = Hash::get($nodePathSegments, 1);

                if (!is_numeric($blockIndex)) {
                    // Try to check if its only one-level deep (only importing one block type)
                    // which is particularly common for JSON.
                    $blockIndex = Hash::get($nodePathSegments, 2);

                    if (!is_numeric($blockIndex)) {
                        $blockIndex = 0;
                    }
                }

                $key = $blockIndex . '.' . $blockHandle . '.' . $subFieldHandle;

                // Check for complex fields (think Table, Super Table, etc), essentially anything that has
                // sub-fields, and doesn't have data directly mapped to the field itself. It needs to be
                // accumulated here (so its in the right order), but grouped based on the field and block
                // its in. A little bit annoying, but no better ideas...
                if ($isComplexField) {
                    $complexFields[$key]['info'] = $subFieldInfo;
                    $complexFields[$key]['data'][$nodePath] = $value;
                    continue;
                }

                // Swap out the node-path stored in the field-mapping info, because
                // it'll be generic MatrixBlock/Images not MatrixBlock/0/Images/0 like we need
                $subFieldInfo['node'] = $nodePath;

                // Parse each field via their own fieldtype service
                $parsedValue = $this->_parseSubField($this->feedData, $subFieldHandle, $subFieldInfo);

                // Finish up with the content, also sort out cases where there's array content
                if (isset($fieldData[$key]) && is_array($fieldData[$key])) {
                    $fieldData[$key] = is_array($parsedValue) ? array_merge_recursive($fieldData[$key], $parsedValue)
                        : $fieldData[$key];
                } else {
                    $fieldData[$key] = $parsedValue;
                }
            }
        }

        // Handle some complex fields that don't directly have nodes, but instead have nested properties mapped.
        // They have their mapping setup on sub-fields, and need to be processed all together, which we've already prepared.
        // Additionally, we only want to supply each field with a sub-set of data related to that specific block and field
        // otherwise, we get the field class processing all blocks in one go - not what we want.
        foreach ($complexFields as $key => $complexInfo) {
            $parts = explode('.', $key);
            $subFieldHandle = $parts[2];

            $subFieldInfo = Hash::get($complexInfo, 'info');
            $nodePaths = Hash::get($complexInfo, 'data');

            $parsedValue = $this->_parseSubField($nodePaths, $subFieldHandle, $subFieldInfo);

            if (isset($fieldData[$key])) {
                $fieldData[$key] = array_merge_recursive($fieldData[$key], $parsedValue);
            } else {
                $fieldData[$key] = $parsedValue;
            }
        }

        ksort($fieldData, SORT_NUMERIC);

        // $order = 0;

        // New, we've got a collection of prepared data, but its formatted a little rough, due to catering for
        // sub-field data that could be arrays or single values. Lets build our Matrix-ready data
        foreach ($fieldData as $blockSubFieldHandle => $value) {
            $handles = explode('.', $blockSubFieldHandle);
            $blockIndex = 'new' . ($handles[0] + 1);
            $blockHandle = $handles[1];
            $subFieldHandle = $handles[2];

            $disabled = Hash::get($this->fieldInfo, 'blocks.' . $blockHandle . '.disabled', false);
            $collapsed = Hash::get($this->fieldInfo, 'blocks.' . $blockHandle . '.collapsed', false);

            // Prepare an array that's ready for Matrix to import it
            $preppedData[$blockIndex . '.type'] = $blockHandle;
            // $preppedData[$blockIndex . '.order'] = $order;
            $preppedData[$blockIndex . '.enabled'] = !$disabled;
            $preppedData[$blockIndex . '.collapsed'] = $collapsed;
            $preppedData[$blockIndex . '.fields.' . $subFieldHandle] = $value;
            // $order++;
        }

        $preppedData = Hash::expand($preppedData);

        return $preppedData;
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $nodePath
     * @param $blocks
     * @return array
     */
    private function _getFieldMappingInfoForNodePath($nodePath, $blocks)
    {
        foreach ($blocks as $blockHandle => $blockInfo) {
            $fields = Hash::get($blockInfo, 'fields');

            $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
            $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

            foreach ($fields as $subFieldHandle => $subFieldInfo) {
                $node = Hash::get($subFieldInfo, 'node');

                $nestedFieldNodes = Hash::extract($subFieldInfo, 'fields.{*}.node');

                if ($nestedFieldNodes) {
                    foreach ($nestedFieldNodes as $key => $nestedFieldNode) {
                        if ($feedPath == $nestedFieldNode) {
                            return [
                                'blockHandle' => $blockHandle,
                                'subFieldHandle' => $subFieldHandle,
                                'subFieldInfo' => $subFieldInfo,
                                'nodePath' => $nodePath,
                                'isComplexField' => true,
                            ];
                        }
                    }
                }

                if ($feedPath == $node || $node === 'usedefault') {
                    return [
                        'blockHandle' => $blockHandle,
                        'subFieldHandle' => $subFieldHandle,
                        'subFieldInfo' => $subFieldInfo,
                        'nodePath' => $nodePath,
                        'isComplexField' => false,
                    ];
                }
            }
        }
    }

    /**
     * @param $feedData
     * @param $subFieldHandle
     * @param $subFieldInfo
     * @return mixed
     */
    private function _parseSubField($feedData, $subFieldHandle, $subFieldInfo)
    {
        $subFieldClassHandle = Hash::get($subFieldInfo, 'field');

        $subField = Hash::extract($this->field->getBlockTypeFields(), '{n}[handle=' . $subFieldHandle . ']')[0];

        $class = Plugin::$plugin->fields->getRegisteredField($subFieldClassHandle);
        $class->feedData = $feedData;
        $class->fieldHandle = $subFieldHandle;
        $class->fieldInfo = $subFieldInfo;
        $class->field = $subField;
        $class->element = $this->element;
        $class->feed = $this->feed;

        // Get our content, parsed by this fields service function
        return $class->parseField();
    }
}
