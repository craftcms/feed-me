<?php

namespace verbb\feedme\fields;

use Cake\Utility\Hash;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DateHelper;

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
