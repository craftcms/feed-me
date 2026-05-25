<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\elements\Entry;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\Plugin;
use craft\fields\ContentBlock as ContentBlockField;

/**
 *
 * @property-read string $mappingTemplate
 */
class ContentBlock extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'ContentBlock';

    /**
     * @var string
     */
    public static string $class = ContentBlockField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/content-block';
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

        $fields = Hash::get($this->fieldInfo, 'fields');

        foreach ($this->feedData as $nodePath => $value) {
            // Get the field mapping info for this node in the feed
            $fieldInfo = $this->_getFieldMappingInfoForNodePath($nodePath, $fields);

            // If this is data concerning our Content Block field
            if ($fieldInfo) {
                $subFieldHandle = $fieldInfo['subFieldHandle'];
                $subFieldInfo = $fieldInfo['subFieldInfo'];
                $isComplexField = $fieldInfo['isComplexField'];

                // Check for complex fields (think Table, Super Table, etc), essentially anything that has
                // sub-fields, and doesn't have data directly mapped to the field itself. It needs to be
                // accumulated here (so its in the right order), but grouped based on the field
                // its in. A bit annoying, but no better ideas...
                if ($isComplexField) {
                    $complexFields[$subFieldHandle]['info'] = $subFieldInfo;
                    $complexFields[$subFieldHandle]['data'][$nodePath] = $value;
                    continue;
                }

                // Swap out the node-path stored in the field-mapping info, because
                // it'll be generic MatrixBlock/Images not MatrixBlock/0/Images/0 like we need
                $subFieldInfo['node'] = $nodePath;

                // Parse each field via their own fieldtype service
                $parsedValue = $this->_parseSubField($this->feedData, $subFieldHandle, $subFieldInfo);

                // Finish up with the content, also sort out cases where there's array content
                if (isset($fieldData[$subFieldHandle]) && is_array($fieldData[$subFieldHandle])) {
                    $fieldData[$subFieldHandle] = is_array($parsedValue) ? array_merge_recursive($fieldData[$subFieldHandle], $parsedValue) : $fieldData[$subFieldHandle];
                } else {
                    $fieldData[$subFieldHandle] = $parsedValue;
                }
            }
        }

        // Handle some complex fields that don't directly have nodes, but instead have nested properties mapped.
        // They have their mapping setup on sub-fields, and need to be processed all together, which we've already prepared.
        // Additionally, we only want to supply each field with a sub-set of data related to that specific field
        // otherwise, we get the field class processing all fields in one go - not what we want.
        foreach ($complexFields as $subFieldHandle => $complexInfo) {
            $subFieldInfo = Hash::get($complexInfo, 'info');
            $nodePaths = Hash::get($complexInfo, 'data');

            $parsedValue = $this->_parseSubField($nodePaths, $subFieldHandle, $subFieldInfo);

            if ($parsedValue !== null) {
                if (isset($fieldData[$subFieldHandle])) {
                    $fieldData[$subFieldHandle] = array_merge_recursive($fieldData[$subFieldHandle], $parsedValue);
                } else {
                    $fieldData[$subFieldHandle] = $parsedValue;
                }
            }
        }

        foreach ($fieldData as $subFieldHandle => $value) {
            $preppedData['fields.' . $subFieldHandle] = $value;
        }

        // if there's nothing in the prepped data,
        // if setEmptyValues is on, return an empty array so that existing blocks are removed,
        // otherwise return null, as if mapping doesn't exist
        if (empty($preppedData)) {
            return $this->feed['setEmptyValues'] ? [] : null;
        }

        return Hash::expand($preppedData);
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $nodePath
     * @return array|null
     */
    private function _getFieldMappingInfoForNodePath($nodePath, $fields): ?array
    {
        $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
        $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

        foreach ($fields as $subFieldHandle => $subFieldInfo) {
            $node = Hash::get($subFieldInfo, 'node');

            $nestedFieldNodes = Hash::extract($subFieldInfo, 'fields.{*}.node');
            $nestedBlockNodes = Hash::extract($subFieldInfo, 'blocks.{*}.{*}.{*}.node');

            if ($nestedBlockNodes) {
                foreach ($nestedBlockNodes as $nestedBlockNode) {
                    if ($feedPath == $nestedBlockNode) {
                        return [
                            'subFieldHandle' => $subFieldHandle,
                            'subFieldInfo' => $subFieldInfo,
                            'nodePath' => $nodePath,
                            'isComplexField' => true,
                        ];
                    }
                }
            }

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
     * Returns all of the content block field's inner fields.
     *
     * @param ContentBlockField $field
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function _getSubFields(ContentBlockField $field): array
    {
        $fields = [];
        foreach ($field->getFieldLayout()?->getCustomFields() as $subField) {
            $fields[] = $subField;
        }

        return $fields;
    }

    /**
     * @param $feedData
     * @param $subFieldHandle
     * @param $subFieldInfo
     * @param $blockHandle entry type handle
     * @return mixed
     */
    private function _parseSubField($feedData, $subFieldHandle, $subFieldInfo): mixed
    {
        $subFieldClassHandle = Hash::get($subFieldInfo, 'field');

        $subField = Hash::extract($this->_getSubFields($this->field), '{n}[handle=' . $subFieldHandle . ']')[0];

        if (
            !$subField instanceof $subFieldClassHandle &&
            ($subField instanceof \craft\fields\Categories || $subField instanceof \craft\fields\Tags)
        ) {
            $subFieldClassHandle = \craft\fields\Entries::class;
        }

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
