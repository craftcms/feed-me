<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\BaseHelper;
use craft\feedme\Plugin;
use craft\fields\data\ColorData;
use craft\fields\Table as TableField;
use craft\helpers\DateTimeHelper;
use craft\helpers\Localization;
use Exception;

/**
 *
 * @property-read string $mappingTemplate
 */
class Table extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Table';

    /**
     * @var string
     */
    public static string $class = TableField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/table';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $parsedData = [];
        $preppedData = [];
        $rowCounter = [];

        $columns = Hash::get($this->fieldInfo, 'fields');

        if (!$columns) {
            return null;
        }

        foreach ($this->feedData as $nodePath => $value) {
            foreach ($columns as $columnHandle => $columnInfo) {

                // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
                // it's supposed to match up with, which is stored in the DB like MatrixBlock/Images
                $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                $node = Hash::get($columnInfo, 'node');
                $type = Hash::get($columnInfo, 'type');

                if ($feedPath == $node || $nodePath == $node) {
                    if (!isset($rowCounter[$columnHandle])) {
                        $rowCounter[$columnHandle] = 0;
                    } else {
                        $rowCounter[$columnHandle]++;
                    }

                    $parsedValue = $this->_handleSubField($type, $value);

                    $parsedData[$rowCounter[$columnHandle]][$columnHandle] = $parsedValue;
                }
            }
        }

        $dataDelimiter = Plugin::$plugin->service->getConfig('dataDelimiter', $this->feed['id']);

        foreach ($parsedData as $rowKey => $row) {
            foreach ($row as $columnKey => $column) {
                if (is_string($column)) {
                    $columnValues = explode($dataDelimiter, $column);

                    if (count($columnValues) > 1) {
                        foreach ($columnValues as $splitRowKey => $columnValue) {
                            $preppedData[$splitRowKey][$columnKey] = $columnValue;
                        }
                    } else {
                        $preppedData[$rowKey][$columnKey] = $column;
                    }
                } else {
                    $preppedData[$rowKey][$columnKey] = $column;
                }
            }
        }

        // Make sure each column has values, even if null
        foreach ($preppedData as $key => $columnData) {
            if (count($columnData) != count($columns)) {
                for ($i = 1, $iMax = count($columns); $i <= $iMax; $i++) {
                    $indexVar = 'col' . $i;
                    if (!isset($preppedData[$key][$indexVar])) {
                        $preppedData[$key]['col' . $i] = null;
                    }
                }
            }
        }

        // Fix keys for columns to be in correct order
        foreach ($preppedData as &$columnData) {
            ksort($columnData, SORT_NATURAL);
        }

        return $preppedData;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param $type
     * @param $value
     * @return bool|ColorData|mixed|string|void|null
     * @throws Exception
     */
    private function _handleSubField($type, $value)
    {
        if ($type == 'checkbox') {
            return BaseHelper::parseBoolean($value);
        }

        if ($type == 'color') {
            return BaseHelper::parseColor($value);
        }

        if ($type == 'date') {
            $parsedValue = DateTimeHelper::toDateTime($value) ?: null;

            return $this->field->serializeValue($parsedValue);
        }

        if ($type == 'lightswitch') {
            return BaseHelper::parseBoolean($value);
        }

        if ($type == 'number') {
            return Localization::normalizeNumber($value);
        }

        if ($type == 'time') {
            $parsedValue = DateTimeHelper::toDateTime($value) ?: null;

            return $this->field->serializeValue($parsedValue);
        }

        // Protect against array values
        if (is_array($value)) {
            $value = '';
        }

        return $value;
    }
}
