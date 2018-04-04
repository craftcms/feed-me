<?php
namespace verbb\feedme\fields;

use verbb\feedme\base\Field;
use verbb\feedme\base\FieldInterface;

use Craft;
use craft\elements\Entry as EntryElement;

use Cake\Utility\Hash;

class Entries extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Entries';
    public static $class = 'craft\fields\Entries';


    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/entries';
    }


    // Public Methods
    // =========================================================================

    public function parseField()
    {
        $value = $this->fetchArrayValue();

        $settings = Hash::get($this->field, 'settings');
        $sources = Hash::get($this->field, 'settings.sources');
        $limit = Hash::get($this->field, 'settings.limit');
        $match = Hash::get($this->fieldInfo, 'options.match', 'title');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');

        $sectionIds = [];

        if (is_array($sources)) {
            foreach ($sources as $source) {
                // When singles is selected as the only option to search in, it doesn't contain any ids...
                if ($source == 'singles') {
                    foreach (craft()->sections->getAllSections() as $section) {
                        $sectionIds[] = ($section->type == 'single') ? $section->id : '';
                    }
                } else {
                    list($type, $id) = explode(':', $source);
                    $sectionIds[] = $id;
                }
            }
        } else if ($sources === '*') {
            $sectionIds = '*';
        }

        $foundElements = [];

        foreach ($value as $dataValue) {
            $query = EntryElement::find();

            $criteria['sectionId'] = $sectionIds;
            $criteria['limit'] = $limit;
            $criteria[$match] = $dataValue;

            Craft::configure($query, $criteria);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            // Check if we should create the element. But only if title is provided (for the moment)
            if (count($ids) == 0) {
                if ($create && $match === 'title') {
                    $foundElements[] = $this->_createElement($dataValue, $sectionIds);
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

    private function _createElement($dataValue, $sectionIds)
    {
        $element = new EntryElement();
        $element->title = $dataValue;
        $element->groupId = $groupId;

        if (!Craft::$app->getElements()->saveElement($element)) {
            throw new \Exception(json_encode($element->getErrors()));
        }

        return $element->id;
    }

}