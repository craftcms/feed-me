<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fields\Dropdown as DropdownField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Dropdown extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Dropdown';

    /**
     * @var string
     */
    public static string $class = DropdownField::class;


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
        $value = $this->fetchValue();

        if ($value === null) {
            return null;
        }

        $value = (string) $value;
        $default = Hash::get($this->fieldInfo, 'default');

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            if (
                (isset($option['value']) && $value === $option[$match]) ||
                ($match === 'label' && $value === $default && $value === $option['value'])
            ) {
                return $option['value'];
            }
        }

        if (empty($value)) {
            return $value;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function fetchValue(): mixed
    {
        return (string) parent::fetchValue();
    }
}
