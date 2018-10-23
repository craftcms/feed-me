<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;

use Craft;
use craft\helpers\Db;

use craft\digitalproducts\Plugin as DigitalProducts;
use craft\digitalproducts\elements\Product as ProductElement;

use Cake\Utility\Hash;

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
        return DigitalProducts::getInstance()->getProductTypes()->getEditableProductTypes();
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
