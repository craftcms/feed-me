<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fields\MultiSelect as MultiSelectField;

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
    public static string $name = 'MultiSelect';

    /**
     * @var string
     */
    public static string $class = MultiSelectField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/option-select';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchArrayValue();
        $default = $this->fetchDefaultArrayValue();

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
