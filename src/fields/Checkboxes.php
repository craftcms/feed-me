<?php

namespace semabit\feedme\fields;

use Cake\Utility\Hash;
use semabit\feedme\base\Field;
use semabit\feedme\base\FieldInterface;
use craft\fields\Checkboxes as CheckboxesField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Checkboxes extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Checkboxes';

    /**
     * @var string
     */
    public static string $class = CheckboxesField::class;

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

        if ($value === null) {
            return null;
        }

        $preppedData = [];

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            foreach ($value as $dataValue) {
                if ($dataValue === $option[$match]) {
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
