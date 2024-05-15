<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element as BaseElement;
use craft\elements\Tag as TagElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\fields\Tags as TagsField;
use craft\helpers\Db;
use craft\helpers\Json;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property-read string $mappingTemplate
 */
class Tags extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Tags';

    /**
     * @var string
     */
    public static string $class = TagsField::class;

    /**
     * @var string
     */
    public static string $elementType = TagElement::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/tags';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField(): mixed
    {
        $value = $this->fetchArrayValue();
        $default = $this->fetchDefaultArrayValue();

        // if the mapped value is not set in the feed
        if ($value === null) {
            return null;
        }

        // if value from the feed is empty and default is not set
        // return an empty array; no point bothering further
        if (empty($default) && DataHelper::isArrayValueEmpty($value)) {
            return [];
        }

        $source = Hash::get($this->field, 'settings.source');
        $limit = Hash::get($this->field, 'settings.limit');
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $match = Hash::get($this->fieldInfo, 'options.match', 'title');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');
        $nodeKey = null;

        // Get tag group id
        [, $groupUid] = explode(':', $source);
        $groupId = Db::idByUid('{{%taggroups}}', $groupUid);

        $foundElements = [];

        foreach ($value as $dataValue) {
            // Prevent empty or blank values (string or array), which match all elements
            if (empty($dataValue) && empty($default)) {
                continue;
            }

            // If we're using the default value - skip, we've already got an id array
            if ($node === 'usedefault') {
                $foundElements = $value;
                break;
            }

            // special provision for falling back on default BaseRelationField value
            // https://github.com/craftcms/feed-me/issues/1195
            if (trim($dataValue) === '') {
                $foundElements = $default;
                break;
            }

            // Because we can match on element attributes and custom fields, AND we're directly using SQL
            // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
            // the content table.
            $columnName = $match;

            if (Craft::$app->getFields()->getFieldByHandle($match)) {
                $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
            }

            $query = TagElement::find();

            // In multi-site, there's currently no way to query across all sites - we use the current site
            // See https://github.com/craftcms/cms/issues/2854
            if (Craft::$app->getIsMultiSite()) {
                if ($targetSiteId) {
                    $criteria['siteId'] = Craft::$app->getSites()->getSiteByUid($targetSiteId)->id;
                } elseif ($feedSiteId) {
                    $criteria['siteId'] = $feedSiteId;
                } else {
                    $criteria['siteId'] = Craft::$app->getSites()->getCurrentSite()->id;
                }
            }

            $criteria['status'] = null;
            $criteria['groupId'] = $groupId;
            $criteria['limit'] = $limit;
            $criteria['where'] = ['=', $columnName, $dataValue];

            Craft::configure($query, $criteria);

            Plugin::info('Search for existing tag with query `{i}`', ['i' => Json::encode($criteria)]);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            Plugin::info('Found `{i}` existing tags: `{j}`', ['i' => count($foundElements), 'j' => Json::encode($foundElements)]);

            // Check if we should create the element. But only if title is provided (for the moment)
            if ((count($ids) == 0) && $create && $match === 'title') {
                $foundElements[] = $this->_createElement($dataValue, $groupId);
            }

            $nodeKey = $this->getArrayKeyFromNode($node);
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        // Check for any sub-fields for the element
        if ($fields) {
            $this->populateElementFields($foundElements, $nodeKey);
        }

        $foundElements = array_unique($foundElements);

        // Protect against sending an empty array - removing any existing elements
        if (!$foundElements) {
            return null;
        }

        return $foundElements;
    }


    // Private Methods
    // =========================================================================

    /**
     * @param $dataValue
     * @param $groupId
     * @return int|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    private function _createElement($dataValue, $groupId): ?int
    {
        $element = new TagElement();
        $element->title = $dataValue;
        $element->groupId = $groupId;

        $siteId = Hash::get($this->feed, 'siteId');

        if ($siteId) {
            $element->siteId = $siteId;
        }

        $element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
            Plugin::error('`{handle}` - Tag error: Could not create - `{e}`.', ['e' => Json::encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            Plugin::info('`{handle}` - Tag `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }
}
