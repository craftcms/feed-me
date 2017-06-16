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

        foreach (Hash::flatten($data) as $key => $value) {
            preg_match('/^(col\d+).*(\d+)$/', $key, $matches);

            if (isset($matches[1]) && $matches[1] != '') {
                $index = $matches[2] . '.data.' . $matches[1];

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