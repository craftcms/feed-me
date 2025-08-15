<?php

namespace craft\feedme\fieldlayoutelements\addresses;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fieldlayoutelements\addresses\AddressField as CraftAddressField;

/**
 * @property-read string $mappingTemplate
 * @since 5.13.0
 */
class AddressField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'AddressField';

    /**
     * @var string
     */
    public static string $class = CraftAddressField::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fieldlayoutelements/addresses/address';
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
