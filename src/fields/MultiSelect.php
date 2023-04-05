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

    /**
     * @var string
     */
    public static $name = 'MultiSelect';

    /**
     * @var string
     */
    public static $class = 'craft\fields\MultiSelect';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/option-select';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField()
    {
        $value = $this->fetchArrayValue();
        $default = $this->fetchDefaultArrayValue();

        if ($value === null) {
            return null;
        }

        $preppedData = [];

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            // Exclude optgroup from available values to match against
            if (isset($option['optgroup'])) {
                continue;
            }
            foreach ($value as $dataValue) {
                if (!empty($dataValue) && $dataValue === $option[$match]) {
                    $preppedData[] = $option['value'];
                }
                // special case for when mapping by label, but also using a default value
                // which relies on $option['value']
                if (empty($dataValue) && in_array($option['value'], $default)) {
                    $preppedData[] = $option['value'];
                }
            }
        }

        return $preppedData;
    }
}
