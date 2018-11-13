<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\BaseHelper;
use verbb\feedme\helpers\DateHelper;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\helpers\Localization;

use Cake\Utility\Hash;

class Table extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Table';
    public static $class = 'craft\fields\Table';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/table';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $parsedData = [];
        $preppedData = [];
        $rowCounter = [];

        $columns = Hash::get($this->fieldInfo, 'fields');

        foreach ($this->feedData as $nodePath => $value) {
            foreach ($columns as $columnHandle => $columnInfo) {

                // Strip out array numbers in the feed path like: MatrixBlock/0/Images/0. We use this to get the field
                // its supposed to match up with, which is stored in the DB like MatrixBlock/Images
                $feedPath = preg_replace('/(\/\d+\/)/', '/', $nodePath);
                $feedPath = preg_replace('/^(\d+\/)|(\/\d+)/', '', $feedPath);

                $node = Hash::get($columnInfo, 'node');
                $handle = Hash::get($columnInfo, 'handle');
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

        $dataDelimiter = FeedMe::$plugin->service->getConfig('dataDelimiter', $this->feed['id']);

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
                for ($i = 1; $i <= count($columns); $i++) { 
                    if (!isset($preppedData[$key]['col' . $i])) {
                        $preppedData[$key]['col' . $i] = null;
                    }
                }
            }
        }

        // Fix keys for columns to be in correct order
        foreach ($preppedData as $key => &$columnData) {
            ksort($columnData, SORT_NATURAL);
        }

        return $preppedData;
    }


    // Private Methods
    // =========================================================================

    private function _handleSubField($type, $value)
    {
        if ($type == 'checkbox') {
            return BaseHelper::parseBoolean($value);
        } else if ($type == 'color') {
            return BaseHelper::parseColor($value);
        } else if ($type == 'date') {
            $parsedValue = DateTimeHelper::toDateTime($value) ?: null;
            
            return $this->field->serializeValue($parsedValue);
        } else if ($type == 'lightswitch') {
            return BaseHelper::parseBoolean($value);
        } else if ($type == 'multiline') {

        } else if ($type == 'number') {
            return Localization::normalizeNumber($value);
        } else if ($type == 'singleline') {

        } else if ($type == 'time') {
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