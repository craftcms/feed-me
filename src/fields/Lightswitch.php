<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\BaseHelper;

class Lightswitch extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Lightswitch';
    public static $class = 'craft\fields\Lightswitch';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/lightswitch';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchValue();

        return $this->parseValue($value);
    }

    public function parseValue($value)
    {
        return BaseHelper::parseBoolean($value);
    }

}
