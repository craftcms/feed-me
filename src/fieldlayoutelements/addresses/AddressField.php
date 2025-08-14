<?php

namespace craft\feedme\fieldlayoutelements\addresses;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\Address as AddressElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\fieldlayoutelements\addresses\AddressField as CraftAddressField;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;

/**
 *
 * @property-read string $mappingTemplate
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

    /**
     * @var string
     */
    public static string $elementType = CraftAddressField::class;


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
