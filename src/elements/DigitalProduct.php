<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\digitalproducts\elements\Product as ProductElement;
use craft\digitalproducts\Plugin as DigitalProducts;
use craft\feedme\base\Element;

class DigitalProduct extends Element
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
        $query = ProductElement::find()
            ->anyStatus()
            ->typeId($settings['elementGroup'][ProductElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
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
