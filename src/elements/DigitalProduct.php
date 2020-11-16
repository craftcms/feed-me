<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\digitalproducts\elements\Product as ProductElement;
use craft\digitalproducts\Plugin as DigitalProducts;
use craft\feedme\base\Element;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read mixed $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class DigitalProduct extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Digital Product';

    /**
     * @var string
     */
    public static $class = 'craft\digitalproducts\elements\Product';

    /**
     * @var
     */
    public $element;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/digital-products/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups()
    {
        if (DigitalProducts::getInstance()) {
            return DigitalProducts::getInstance()->getProductTypes()->getEditableProductTypes();
        }
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, $params = [])
    {
        $query = ProductElement::find()
            ->anyStatus()
            ->typeId($settings['elementGroup'][ProductElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\Carbon\Carbon|\DateTime|false|string|null
     */
    protected function parsePostDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|\Carbon\Carbon|\DateTime|false|string|null
     */
    protected function parseExpiryDate($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }
}
