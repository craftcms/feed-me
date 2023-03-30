<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;

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
    public static $name = 'Dropdown';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Dropdown';


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
        $value = $this->fetchValue();

        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        $options = Hash::get($this->field, 'settings.options');
        $match = Hash::get($this->fieldInfo, 'options.match', 'value');

        foreach ($options as $option) {
            if (isset($option['value']) && $value === $option[$match]) {
                return $option['value'];
            }
        }

        if (empty($value)) {
            return $value;
        }

        return null;
    }
}
