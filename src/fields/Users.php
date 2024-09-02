<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element as BaseElement;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\UserQuery;
use craft\elements\User as UserElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\helpers\DataHelper;
use craft\feedme\Plugin;
use craft\fields\Users as UsersField;
use craft\helpers\Db;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\services\ElementSources;
use Throwable;
use yii\base\Exception;

/**
 *
 * @property-read string $mappingTemplate
 */
class Users extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'Users';

    /**
     * @var string
     */
    public static string $class = UsersField::class;

    /**
     * @var string
     */
    public static string $elementType = UserElement::class;


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/fields/users';
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

        $sources = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.maxRelations');
        $match = Hash::get($this->fieldInfo, 'options.match', 'email');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');
        $nodeKey = null;

        // Get source id's for connecting
        $groupIds = [];
        $customSources = [];
        $isAdmin = false;
        $status = null;

        if (is_array($sources)) {
            // go through sources that start with "group:" and get group uid for those
            foreach ($sources as $source) {
                if (str_starts_with($source, 'custom:')) {
                    $customSources[] = ElementHelper::findSource(UserElement::class, $source, ElementSources::CONTEXT_MODAL);
                }
                if (str_starts_with($source, 'group:')) {
                    [, $uid] = explode(':', $source);
                    $groupIds[] = Db::idByUid('{{%usergroups}}', $uid);
                }
            }

            // the other possible source in Craft 4 can be 'admins' for which we'll need a separate query
            if (in_array('admins', $sources, true)) {
                $isAdmin = true;
            }

            // the other possible source in Craft 4 can be 'credentialed'
            if (in_array(UserQuery::STATUS_CREDENTIALED, $sources, true)) {
                $status[] = UserQuery::STATUS_CREDENTIALED;
            }
            // or 'inactive'
            if (in_array(UserElement::STATUS_INACTIVE, $sources, true)) {
                $status[] = UserElement::STATUS_INACTIVE;
            }

            // if there's only one source, and it's a custom source, make sure $create is nullified;
            // we don't want to create users for custom sources because of ensuring all the conditions are met
            if (count($sources) == 1 && !empty($customSources)) {
                $create = null;
            }
        } elseif ($sources === '*') {
            $groupIds = null;
        }

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
            if (DataHelper::isArrayValueEmpty($value)) {
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

            $ids = [];
            $criteria['status'] = null;
            $criteria['limit'] = $limit;
            $criteria['where'] = ['=', $columnName, $dataValue];

            // If the only source for the Users field is "admins" we don't have to bother with this query.
            if (!($isAdmin && empty($groupIds) && empty($customSources))) {
                $ids = $this->_findUsers($criteria, $groupIds, $customSources);
                $foundElements = array_merge($foundElements, $ids);
            }

            // Previous query would look through selected groups or if "all" was selected
            // (in which case groupIds would be null, and wouldn't actually limit the query).
            // So if we haven't found a match with the previous query, and field sources contains "admins",
            // we have to look for the user among admins too.
            if ($isAdmin && count($ids) === 0) {
                unset($criteria['groupId']);
                $criteria['admin'] = true;

                $ids = $this->_findUsers($criteria);
                $foundElements = array_merge($foundElements, $ids);
            }

            // If we still have no matches, check based on the credentialed/inactive status
            if (!empty($status) && count($ids) === 0) {
                unset($criteria['groupId'], $criteria['admin']);
                $criteria['status'] = $status;

                $ids = $this->_findUsers($criteria);
                $foundElements = array_merge($foundElements, $ids);
            }

            // Check if we should create the element. But only if email is provided (for the moment)
            if ((count($ids) == 0) && $create && $match === 'email') {
                $foundElements[] = $this->_createElement($dataValue, $groupIds);
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
        $element = new UserElement();
        $element->username = $dataValue;
        $element->email = $dataValue;

        if ($groupId) {
            $element->groupId = $groupId;
        }

        $siteId = Hash::get($this->feed, 'siteId');

        if ($siteId) {
            $element->siteId = $siteId;
        }

        $element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
            Plugin::error('`{handle}` - User error: Could not create - `{e}`.', ['e' => Json::encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            Plugin::info('`{handle}` - User `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }

    /**
     * Attempt to find User based on search criteria. Return array of found IDs.
     *
     * @param $criteria
     * @return array|int[]
     */
    private function _findUsers($criteria, $groupIds = null, $customSources = null): array
    {
        $query = UserElement::find();
        Craft::configure($query, $criteria);

        // if we have any custom sources, we want to modify the query to account for those
        if (!empty($customSources)) {
            $conditionsService = Craft::$app->getConditions();
            foreach ($customSources as $customSource) {
                /** @var ElementConditionInterface $sourceCondition */
                $sourceCondition = $conditionsService->createCondition($customSource['condition']);
                $sourceCondition->modifyQuery($query);
            }
        }

        if (!empty($groupIds)) {
            // now that the custom sources have been accounted for,
            // we can adjust the group id to include any regular, group sources (group ids)
            $query->groupId = array_merge($query->groupId ?? [], $groupIds);
        }

        // we're getting the criteria from conditions now too, so they are not included in the $criteria array;
        // so, we get all the query criteria, filter out any empty or boolean ones and only show the ones that look to be filled out
        $showCriteria = $criteria;
        $allCriteria = $query->getCriteria();
        foreach ($allCriteria as $key => $criterion) {
            if (!empty($criterion) && !is_bool($criterion)) {
                $showCriteria[$key] = $criterion;
            }
        }

        Plugin::info('Search for existing user with query `{i}`', ['i' => json_encode($showCriteria)]);

        $ids = $query->ids();

        Plugin::info('Found `{i}` existing users: `{j}`', ['i' => count($ids), 'j' => json_encode($ids)]);

        return $ids;
    }
}
