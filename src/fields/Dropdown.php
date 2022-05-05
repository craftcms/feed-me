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

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            if (isset($option['value']) && $value === $option[$match]) {
                return $option['value'];
            }
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
