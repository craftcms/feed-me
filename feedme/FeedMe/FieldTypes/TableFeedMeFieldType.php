<?php
namespace Craft;

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

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        $fieldData = array();

        $next = reset($data);

        // When we import a non-repeatable node into a table, we must ensure its treated consistently
        // Because Table/Row/Column1 is not the same as Table/Row/.../Column1 - it should be the latter
        if (!is_array($next)) {
            foreach ($data as $columnHandle => $row) {
                $data[$columnHandle] = array($row);
            }
        }

        // And an even more special-case, when use it Matrix 'Matrix/MatrixItem/.../Table/Row/.../Column1'
        // we need to process it a little differently. Notice the two repeatable nodes.
        if (substr_count($options['feedHandle'][0], '/.../') == 2) {
            $next = reset($data);
            $next = reset($next);

            if (is_array($next)) {
                foreach ($data as $i => $row) {
                    foreach ($row as $j => $column) {
                        foreach ($column as $k => $col) {
                            // Check for false for checkbox
                            if ($col === 'false') {
                                $col = null;
                            }

                            $fieldData[$k][($j+1)][$i] = $col;
                        }
                    }
                }

                return $fieldData;
            }
        }

        foreach ($data as $i => $row) {
            foreach ($row as $j => $column) {
                // Check for false for checkbox
                if ($column === 'false') {
                    $column = null;
                }

                // Actually need to invert keys. Feed-mapping will deliver feed data as:
                // array: {
                //   col1: {
                //     0: val,
                //     1: val,
                //   }
                //   col2: {
                //     0: val,
                //     1: val,
                //   }
                // }
                // We need to convert this to:
                // array: {
                //   0: {
                //     col1: val,
                //     col2: val,
                //   }
                //   1: {
                //     col1: val,
                //     col2: val,
                //   }
                // }

                $fieldData[($j+1)][$i] = $column;
            }
        }

        return $fieldData;
    }
    
}