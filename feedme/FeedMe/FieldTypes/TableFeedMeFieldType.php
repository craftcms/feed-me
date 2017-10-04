<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class TableFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feedme/_includes/fields/table';
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

        /*foreach (Hash::flatten($data) as $key => $value) {
            preg_match('/^(col\d+)/', $key, $matches);

            if (isset($matches[1]) && $matches[1] != '') {
                $index = $matches[1];
            } else {
                $index = 0;
            }

            $parsedData[$index][] = $value;
        }*/

        if (Hash::dimensions($data) == 2) {
            $data = array($data);
        }

        // Normalise some data
        $parsedData = array();

        // Normalise row data
        foreach (Hash::flatten($data) as $key => $value) {
            preg_match('/(col\d+)/', $key, $colMatches);

            $colIndex = Hash::get($colMatches, '1');

            if (!is_null($colIndex)) {
                $parsedData[$colIndex][] = $value;
            }
        }

        $data = Hash::expand($parsedData);

        // Normalise some data
        $parsedData = array();

        foreach (Hash::flatten($data) as $key => $value) {
            preg_match('/^(col\d+)/', $key, $colMatches);
            preg_match('/(\d+)$/', $key, $rowMatches);

            $colIndex = Hash::get($colMatches, '1');
            $rowIndex = Hash::get($rowMatches, '1');

            if (!is_null($colIndex) && !is_null($rowIndex)) {
                $index = $rowIndex . '.data.' . $colIndex;
                $parsedData[$index] = $value;
            } else {
                $parsedData[$key] = $value;
            }
        }

        $data = Hash::expand($parsedData);
    
        foreach ($data as $i => $row) {
            if (isset($row['data'])) {
                $row = $row['data'];
            }

            foreach ($row as $j => $column) {
                if (!isset($column['data'])) {
                    $column = array('data' => $column);
                }

                // Check for false for checkbox
                if ($column['data'] === 'false') {
                    $column['data'] = null;
                }

                $preppedData[($i)][$j] = $column['data'];
            }
        }

        return $preppedData;
    }
    
}