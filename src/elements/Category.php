<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;

use Craft;
use craft\elements\Category as CategoryElement;

use Cake\Utility\Hash;

class Category extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Category';
    public static $class = 'craft\elements\Category';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/category/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/category/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/category/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return Craft::$app->categories->getEditableGroups();
    }

    public function getQuery($settings, $params = [])
    {
        $query = CategoryElement::find();

        $criteria = array_merge([
            'status' => null,
            'groupId' => $settings['elementGroup'][CategoryElement::class],
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
        $this->element = new CategoryElement();
        $this->element->groupId = $settings['elementGroup'][CategoryElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

    public function afterSave($data, $settings)
    {
        $parent = Hash::get($data, 'parent');

        if ($parent && $parent !== $this->element->id) {
            $parentCategory = CategoryElement::findOne(['id' => $parent]);

            Craft::$app->getStructures()->append($this->element->group->structureId, $this->element, $parentCategory);
        }
    }

    // Protected Methods
    // =========================================================================

    protected function parseParent($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $match = 'id';
        }

        $element = CategoryElement::findOne([$match => $value]);

        if ($element) {
            return $element->id;
        }

        // Check if we should create the element. But only if title is provided (for the moment)
        if ($create && $match === 'title') {
            $element = new CategoryElement();
            $element->title = $value;
            $element->groupId = $this->element->groupId;

            if (!Craft::$app->getElements()->saveElement($element)) {
                FeedMe::error(null, 'Category error: Could not create parent - ' . json_encode($element->getErrors()));
            }

            FeedMe::info(null, 'Category ' . $element->id . ' added.');

            return $element->id;
        }

        return null;
    }

}
