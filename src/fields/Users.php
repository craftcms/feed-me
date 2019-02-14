<?php
namespace verbb\feedme\fields;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;
use craft\elements\User as UserElement;
use craft\helpers\Db;

use Cake\Utility\Hash;

class Users extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Users';
    public static $class = 'craft\fields\Users';
    public static $elementType = 'craft\elements\User';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/users';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
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
                list($type, $uid) = explode(':', $source);
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

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            // Check if we should create the element. But only if email is provided (for the moment)
            if (count($ids) == 0) {
                if ($create && $match === 'email') {
                    $foundElements[] = $this->_createElement($dataValue, $groupIds);
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
        $element = new UserElement();
        $element->username = $dataValue;
        $element->email = $dataValue;

        if ($groupId) {
            $element->groupId = $groupId;
        }

        $siteId = Hash::get($this->feed, 'siteId');
        $propagate = $siteId ? false : true;

        if ($siteId) {
            $element->siteId = $siteId;
        }

        if (!Craft::$app->getElements()->saveElement($element, true, $propagate)) {
            FeedMe::error('`{handle}` - User error: Could not create - `{e}`.', ['e' => json_encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            FeedMe::info('`{handle}` - User `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }

}
