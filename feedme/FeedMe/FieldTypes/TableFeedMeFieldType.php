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
            return;
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
    
        foreach ($data as $i => $row) {
            foreach ($row as $j => $column) {
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