<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DateHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class Date extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Date';
    public static $class = 'craft\fields\Date';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/date';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchValue();

        $formatting = Hash::get($this->fieldInfo, 'options.match');

        $dateValue = DateHelper::parseString($value, $formatting);

        if ($dateValue) {
            return $dateValue;
        }

        return $value;
    }

}
