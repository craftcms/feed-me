<?php

namespace verbb\feedme\fields;

use craft\helpers\Localization;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

class Number extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Number';
    public static $class = 'craft\fields\Number';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/default';
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
        return Localization::normalizeNumber($value);
    }

}
