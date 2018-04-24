<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DataHelper;

use Craft;
use craft\db\Query;

use Cake\Utility\Hash;

class Matrix extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Matrix';
    public static $class = 'craft\fields\Matrix';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/matrix';
    }


    // Public Methods
    // =========================================================================

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
            $blockCount = 0;

            // Then, loop through all our block definitions to find the correct field definition
            foreach ($blocks as $blockHandle => $blockInfo) {
                $fields = Hash::get($blockInfo, 'fields');

                $blockCount++;

                // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
                // its supposed to match up with, which is stored in the DB like MatrixBlock/Images
                $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                // Find the field information we're mapping data to
                foreach ($fields as $subFieldHandle => $subFieldInfo) {
                    $node = Hash::get($subFieldInfo, 'node');

                    // Try to find the index of the Matrix Block this data sits under.
                    // If this is a single Matrix block, it won't be an array (so it won't have an index)
                    // So we force it to either get the MatrixBlock/1/Images index, or force it to '0'
                    preg_match('/\/(\d+)\//', $nodePath, $matches);
                    $blockIndex = Hash::get($matches, '1', $blockCount);

                    // Check for complex fields (think Table, Super Table, etc), essentially anything that has
                    // sub-fields, and doesn't have data directly mapped to the field itself. It needs to be
                    // accumulated here (so its in the right order), but grouped based on the field and block
                    // its in. A little bit annoying, but no better ideas...
                    $fieldFieldHandles = Hash::extract($subFieldInfo, 'fields.{*}.node');

                    if (in_array($feedPath, $fieldFieldHandles)) {
                        $complexFields[$subFieldHandle . '_' . $blockIndex]['info'] = $subFieldInfo;
                        $complexFields[$subFieldHandle . '_' . $blockIndex]['data'][$nodePath] = $value;
                        continue;
                    }

                    if ($feedPath == $node) {
                        // Before we do anything, we need to parse the inner-field data for each fieldtype
                        // But we need to swap out the node-path stored in the field-mapping info, because
                        // it'll be generic MatrixBlock/Images not MatrixBlock/0/Images/0 like we need
                        $subFieldInfo['node'] = $nodePath;

                        $fieldData = $this->_parseSubField($this->feedData, $subFieldHandle, $subFieldInfo, $blockIndex, $blockHandle, $fieldData);
                    }
                }
            }
        }

        // Handle some complex fields that don't directly have nodes, but instead have nested properties mapped.
        // They have their mapping setup on sub-fields, and need to be processed all together, which we've already prepared.
        // Additionally, we only want to supply each field with a sub-set of data related to that specific block and field
        // otherwise, we get the field class processing all blocks in one go - not what we want.
        foreach ($complexFields as $fieldHandleBlockIndex => $complexInfo) {
            $parts = explode('_', $fieldHandleBlockIndex);
            $subFieldHandle = $parts[0];
            $blockIndex = $parts[1];

            $subFieldInfo = Hash::get($complexInfo, 'info');
            $nodePaths = Hash::get($complexInfo, 'data');

            $fieldData = $this->_parseSubField($nodePaths, $subFieldHandle, $subFieldInfo, $blockIndex, $blockHandle, $fieldData);
        }

        ksort($fieldData, SORT_NUMERIC);

        $order = 0;

        // New, we've got a collection of prepared data, but its formatted a little rough, due to catering for
        // sub-field data that could be arrays or single values. Lets build our Matrix-ready data
        foreach ($fieldData as $blockSubFieldHandle => $value) {
            $handles = explode('.', $blockSubFieldHandle);
            $blockIndex = 'new' . $handles[0];
            $blockHandle = $handles[1];
            $subFieldHandle = $handles[2];

            $enabled = Hash::get($this->fieldInfo, 'blocks.' . $blockHandle . '.enabled');
            $collapsed = Hash::get($this->fieldInfo, 'blocks.' . $blockHandle . '.collapsed');

            // Prepare an array thats ready for Matrix to import it
            $preppedData[$blockIndex . '.type'] = $blockHandle;
            $preppedData[$blockIndex . '.order'] = $order;
            $preppedData[$blockIndex . '.enabled'] = $enabled;
            $preppedData[$blockIndex . '.collapsed'] = $collapsed;
            $preppedData[$blockIndex . '.fields.' . $subFieldHandle] = $value;

            $order++;
        }

        $preppedData = Hash::expand($preppedData);

        return $preppedData;
    }


    // Private Methods
    // =========================================================================

    private function _parseSubField($feedData, $subFieldHandle, $subFieldInfo, $blockIndex, $blockHandle, $fieldData)
    {
        $subFieldClassHandle = Hash::get($subFieldInfo, 'field');

        $subField = Hash::extract($this->field->getBlockTypeFields(), '{n}[handle=' . $subFieldHandle . ']')[0];

        $class = FeedMe::$plugin->fields->getRegisteredField($subFieldClassHandle);
        $class->feedData = $feedData;
        $class->fieldHandle = $subFieldHandle;
        $class->fieldInfo = $subFieldInfo;
        $class->field = $subField;
        $class->element = $this->element;
        $class->feed = $this->feed;

        // Get our content, parsed by this fields service function
        $parsedValue = $class->parseField();

        // Create a new index with 'new1' plus the sub-field handle
        $blockSubFieldHandle = ($blockIndex + 1) . '.' . $blockHandle . '.' . $subFieldHandle;

        // Store and we're done
        return array_merge_recursive($fieldData, [$blockSubFieldHandle => $parsedValue]);
    }

    // private function _parseNodeTree(&$tree, $array, $index = '') {
    //     foreach ($array as $key => $value) {
    //         $node = Hash::get($value, 'node');
    //         $fields = Hash::get($value, 'fields');

    //         if ($node) {
    //             $tree[$node] = $index . $key;
    //         }

    //         if ($fields) {
    //             $this->_parseNodeTree($tree, $fields, $index . $key . '.fields.');
    //         }
    //     }
    // }
}
