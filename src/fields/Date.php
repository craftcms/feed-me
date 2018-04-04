<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;
use verbb\feedme\helpers\DateHelper;

use Craft;

use Cake\Utility\Hash;

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
        $value = $this->fetchSimpleValue();
        
        $formatting = Hash::get($this->fieldInfo, 'options.match');

        $dateValue = DateHelper::parseString($value, $formatting);

        if ($dateValue) {
            return $dateValue;
        }

        return $value;
    }

}