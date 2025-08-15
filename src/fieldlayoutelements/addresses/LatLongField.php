<?php

namespace craft\feedme\fieldlayoutelements\addresses;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fieldlayoutelements\addresses\LatLongField as CraftLatLongField;

/**
 * @property-read string $mappingTemplate
 * @since 5.13.0
 */
class LatLongField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'LatLongField';

    /**
     * @var string
     */
    public static string $class = CraftLatLongField::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fieldlayoutelements/addresses/lat-long';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        return [];
    }
}
