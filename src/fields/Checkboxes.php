<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
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
        $customOptions = Hash::get($this->field, 'settings.customOptions', false);
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            foreach ($value as $key => $dataValue) {
                if ($dataValue === $option[$match]) {
                    $preppedData[] = $option['value'];
                    unset($value[$key]);
                }

                // special case for when mapping by label, but also using a default value
                // which relies on $option['value']
                if (empty($dataValue) && in_array($option['value'], $default)) {
                    $preppedData[] = $option['value'];
                    unset($value[$key]);
                }
            }
        }

        // if custom options are allowed, and we still have values left in the $value variable - process those too
        if ($customOptions && !empty($value)) {
            foreach ($value as $dataValue) {
                $preppedData[] = $dataValue;
            }
        }

        return $preppedData;
    }
}
