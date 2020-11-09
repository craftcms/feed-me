<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\helpers\Localization;

/**
 *
 * @property-read string $mappingTemplate
 */
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
