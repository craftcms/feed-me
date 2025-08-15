<?php

namespace craft\feedme\fieldlayoutelements\addresses;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fieldlayoutelements\FullNameField as CraftFullNameField;

/**
 * @property-read string $mappingTemplate
 * @since 5.13.0
 */
class FullNameField extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'FullNameField';

    /**
     * @var string
     */
    public static string $class = CraftFullNameField::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fieldlayoutelements/full-name';
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
