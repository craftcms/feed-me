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

    /**
     * @var string
     */
    public static $name = 'Number';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Number';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/default';
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

        return $this->parseValue($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function parseValue($value)
    {
        return Localization::normalizeNumber($value);
    }
}
