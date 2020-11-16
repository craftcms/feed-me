<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\BaseHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class Lightswitch extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Lightswitch';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Lightswitch';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/lightswitch';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField()
    {
        $value = $this->fetchValue();

        return $this->parseValue($value);
    }

    /**
     * @inheritDoc
     */
    public function parseValue($value)
    {
        return BaseHelper::parseBoolean($value);
    }
}
