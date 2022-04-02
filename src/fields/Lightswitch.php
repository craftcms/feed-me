<?php

namespace craft\feedme\fields;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\BaseHelper;
use craft\fields\Lightswitch as LightswitchField;

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
    public static string $name = 'Lightswitch';

    /**
     * @var string
     */
    public static string $class = LightswitchField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/lightswitch';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchValue();

        return $this->parseValue($value);
    }

    public function parseValue($value)
    {
        return BaseHelper::parseBoolean($value);
    }
}
