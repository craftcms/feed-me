<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;
use craft\commerce\elements\Product as ProductElement;

use Cake\Utility\Hash;

class CommerceProducts extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'CommerceProducts';
    public static $class = 'craft\fields\CommerceProducts';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/commerce_products';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
        $sources = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.limit');
        $match = Hash::get($this->fieldInfo, 'options.match', 'title');

        $typeIds = [];

        if (is_array($sources)) {
            foreach ($sources as $type) {
                list(, $id) = explode(':', $type);
            }
        } else if ($sources === '*') {
            $typeIds = '*';
        }

        $foundElements = [];

        foreach ($value as $dataValue) {
            $query = ProductElement::find();

            $criteria['typeId'] = $typeIds;
            $criteria['limit'] = $limit;
            $criteria[$match] = $dataValue;

            Craft::configure($query, $criteria);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        return $foundElements;
    }

}