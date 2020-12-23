<?php

namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element as BaseElement;
use craft\elements\User as UserElement;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\Plugin;
use craft\helpers\Db;

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
    public static $name = 'Users';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Users';

    /**
     * @var string
     */
    public static $elementType = 'craft\elements\User';


    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/users';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $sources = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.limit');
        $match = Hash::get($this->fieldInfo, 'options.match', 'email');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');

        // Get source id's for connecting
        $groupIds = [];

        if (is_array($sources)) {
            foreach ($sources as $source) {
                list(, $uid) = explode(':', $source);
                $groupIds[] = Db::idByUid('{{%usergroups}}', $uid);
            }
        } else if ($sources === '*') {
            $groupIds = null;
        }

        $foundElements = [];

        if (!$value) {
            return $foundElements;
        }

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

            // Because we can match on element attributes and custom fields, AND we're directly using SQL
            // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
            // the content table.
            $columnName = $match;

            if (Craft::$app->getFields()->getFieldByHandle($match)) {
                $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
            }

            $query = UserElement::find();

            $criteria['status'] = null;
            $criteria['groupId'] = $groupIds;
            $criteria['limit'] = $limit;
            $criteria['where'] = ['=', $columnName, $dataValue];

            Craft::configure($query, $criteria);

            Plugin::info('Search for existing user with query `{i}`', ['i' => json_encode($criteria)]);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            Plugin::info('Found `{i}` existing users: `{j}`', ['i' => count($foundElements), 'j' => json_encode($foundElements)]);

            // Check if we should create the element. But only if email is provided (for the moment)
            if ((count($ids) == 0) && $create && $match === 'email') {
                $foundElements[] = $this->_createElement($dataValue, $groupIds);
            }
        }

        // Check for field limit - only return the specified amount
        if ($foundElements && $limit) {
            $foundElements = array_chunk($foundElements, $limit)[0];
        }

        // Check for any sub-fields for the element
        if ($fields) {
            $this->populateElementFields($foundElements);
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
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    private function _createElement($dataValue, $groupId)
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

        if (!Craft::$app->getElements()->saveElement($element)) {
            Plugin::error('`{handle}` - User error: Could not create - `{e}`.', ['e' => json_encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            Plugin::info('`{handle}` - User `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }
}
