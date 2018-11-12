<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;
use craft\elements\Category as CategoryElement;
use craft\helpers\Db;

use Cake\Utility\Hash;

class Categories extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Categories';
    public static $class = 'craft\fields\Categories';
    public static $elementType = 'craft\elements\Category';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/categories';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
        $source = Hash::get($this->field, 'settings.source');
        $limit = Hash::get($this->field, 'settings.limit');
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $match = Hash::get($this->fieldInfo, 'options.match', 'title');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');

        // Get source id's for connecting
        list($type, $groupId) = explode(':', $source);

        $foundElements = [];

        foreach ($value as $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            if (empty($dataValue)) {
                continue;
            }

            // If we're using the default value - skip, we've already got an id array
            if ($node === 'usedefault') {
                $foundElements = $value;
                break;
            }

            $query = CategoryElement::find();

            // In multi-site, there's currently no way to query across all sites - we use the current site
            // See https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
                if ($targetSiteId) {
                    $criteria['siteId'] = $targetSiteId;
                } else if ($feedSiteId) {
                    $criteria['siteId'] = $feedSiteId;
                } else {
                    $criteria['siteId'] = Craft::$app->getSites()->getCurrentSite()->id;
                }
            }

            // Because we can match on element attributes and custom fields, AND we're directly using SQL
            // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
            // the content table.
            $columnName = $match;

            if (Craft::$app->getFields()->getFieldByHandle($match)) {
                $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
            }

            $criteria['status'] = null;
            $criteria['groupId'] = $groupId;
            $criteria['limit'] = $limit;
            $criteria['where'] = ['=', $columnName, $dataValue];

            Craft::configure($query, $criteria);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            // Check if we should create the element. But only if title is provided (for the moment)
            if (count($ids) == 0) {
                if ($create && $match === 'title') {
                    $foundElements[] = $this->_createElement($dataValue, $groupId);
                }
            }
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        // Check for any sub-fields for the lement
        if ($fields) {
            $this->populateElementFields($foundElements);
        }

        $foundElements = array_unique($foundElements);

        return $foundElements;
    }


    // Private Methods
    // =========================================================================

    private function _createElement($dataValue, $groupId)
    {
        $element = new CategoryElement();
        $element->title = $dataValue;
        $element->groupId = $groupId;

        if (!Craft::$app->getElements()->saveElement($element)) {
            FeedMe::error('`{handle}` - Category error: Could not create - `{e}`.', ['e' => json_encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            FeedMe::info('`{handle}` - Category `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }

}