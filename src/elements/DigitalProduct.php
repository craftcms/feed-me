<?php

namespace verbb\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\digitalproducts\elements\Product as ProductElement;
use craft\digitalproducts\Plugin as DigitalProducts;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;

class DigitalProduct extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Digital Product';
    public static $class = 'craft\digitalproducts\elements\Product';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        if (DigitalProducts::getInstance()) {
            return DigitalProducts::getInstance()->getProductTypes()->getEditableProductTypes();
        }
    }

    public function getQuery($settings, $params = [])
    {
        $query = ProductElement::find();

        $criteria = array_merge([
            'status' => null,
            'typeId' => $settings['elementGroup'][ProductElement::class],
        ], $params);

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $criteria['siteId'] = $siteId;
        }

        Craft::configure($query, $criteria);

        return $query;
    }

    public function setModel($settings)
    {
        $this->element = new ProductElement();
        $this->element->typeId = $settings['elementGroup'][ProductElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }


    // Protected Methods
    // =========================================================================

    protected function parsePostDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    protected function parseExpiryDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

}
