<?php

namespace craft\feedme\fieldlayoutelements\assets;

use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\fieldlayoutelements\assets\AltField;

/**
 *
 * @property-read string $mappingTemplate
 */
class Alt extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Alt';

    /**
     * @var string
     */
    public static string $class = AltField::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fieldlayoutelements/assets/alt';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        // used when importing into Asset element directly
        // when importing into the sub-fields of the assets field, this will go through src/fields/Assets.php->parseField()
        $value = $this->fetchValue();

        if ($value === null) {
            return null;
        }

        return $value;
    }
}
