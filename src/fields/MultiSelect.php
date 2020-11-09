<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;

/**
 *
 * @property-read string $mappingTemplate
 */
class MultiSelect extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'MultiSelect';
    public static $class = 'craft\fields\MultiSelect';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/option-select';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $preppedData = [];

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            foreach ($value as $dataValue) {
                if ($dataValue === $option[$match]) {
                    $preppedData[] = $option['value'];
                }
            }
        }

        return $preppedData;
    }

}
