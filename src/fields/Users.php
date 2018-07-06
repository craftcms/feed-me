<?php
namespace verbb\feedme\fields;

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
                list($type, $id) = explode(':', $source);
                $groupIds[] = $id;
            }
        } else if ($sources === '*') {
            $groupIds = '';
        }

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
            
            $query = UserElement::find();

            $criteria['status'] = null;
            $criteria['groupId'] = $groupIds;
            $criteria['limit'] = $limit;
            $criteria[$match] = Db::escapeParam($dataValue);

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

        return $foundElements;
    }



    // Private Methods
    // =========================================================================

    private function _createElement($dataValue, $groupId)
    {
        $element = new TagElement();
        $element->email = $dataValue;
        $element->groupId = $groupId;

        if (!Craft::$app->getElements()->saveElement($element)) {
            throw new \Exception(json_encode($element->getErrors()));
        }

        return $element->id;
    }

}