<?php

namespace semabit\feedme\fields;

use semabit\feedme\base\Field;
use semabit\feedme\base\FieldInterface;
use semabit\feedme\helpers\BaseHelper;
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

        if ($value === null) {
            return null;
        }

        return $this->parseValue($value);
    }

    public function parseValue($value)
    {
        return BaseHelper::parseBoolean($value);
    }
}
