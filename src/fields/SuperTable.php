<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\Plugin;
use verbb\supertable\fields\SuperTableField;

/**
 *
 * @property-read string $mappingTemplate
 */
class SuperTable extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'SuperTable';

    /**
     * @var string
     */
    public static string $class = SuperTableField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/super-table';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $preppedData = [];
        $fieldData = [];
        $complexFields = [];

        $blockTypeId = Hash::get($this->fieldInfo, 'blockTypeId');
        $fields = Hash::get($this->fieldInfo, 'fields');

        if (!$blockTypeId) {
            return null;
        }

        foreach ($this->feedData as $nodePath => $value) {
            // Get the field mapping info for this node in the feed
            $fieldInfo = $this->_getFieldMappingInfoForNodePath($nodePath, $fields);

            // If this is data concerning our Super Table field and blocks
            if ($fieldInfo) {
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

                $key = $blockIndex . '.' . $subFieldHandle;

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
                    $fieldData[$key] = array_merge_recursive($fieldData[$key], $parsedValue);
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
            $subFieldHandle = $parts[1];

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

        $order = 0;

        // New, we've got a collection of prepared data, but its formatted a little rough, due to catering for
        // sub-field data that could be arrays or single values. Let's build our Matrix-ready data
        foreach ($fieldData as $blockSubFieldHandle => $value) {
            $handles = explode('.', $blockSubFieldHandle);
            $blockIndex = 'new' . ($handles[0] + 1);
            $subFieldHandle = $handles[1];

            // Prepare an array that's ready for Matrix to import it
            $preppedData[$blockIndex . '.type'] = $blockTypeId;
            $preppedData[$blockIndex . '.order'] = $order;
            $preppedData[$blockIndex . '.enabled'] = true;
            $preppedData[$blockIndex . '.fields.' . $subFieldHandle] = $value;

            $order++;
        }

        return Hash::expand($preppedData);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param $nodePath
     * @param $fields
     * @return array|null
     */
    private function _getFieldMappingInfoForNodePath($nodePath, $fields): ?array
    {
        $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
        $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $node = Hash::get($subFieldInfo, 'node');

            $nestedFieldNodes = Hash::extract($subFieldInfo, 'fields.{*}.node');

            if ($nestedFieldNodes) {
                foreach ($nestedFieldNodes as $nestedFieldNode) {
                    if ($feedPath == $nestedFieldNode) {
                        return [
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
                    'subFieldHandle' => $subFieldHandle,
                    'subFieldInfo' => $subFieldInfo,
                    'nodePath' => $nodePath,
                    'isComplexField' => false,
                ];
            }
        }

        return null;
    }

    /**
     * @param $feedData
     * @param $subFieldHandle
     * @param $subFieldInfo
     * @return mixed
     */
    private function _parseSubField($feedData, $subFieldHandle, $subFieldInfo): mixed
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
