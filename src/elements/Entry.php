<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;
use verbb\feedme\helpers\DateHelper;

use Craft;
use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\helpers\Db;
use craft\models\Section;

use Cake\Utility\Hash;

class Entry extends Element implements ElementInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Entry';
    public static $class = 'craft\elements\Entry';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/entry/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/entry/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/entry/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        // Get editable sections for user
        $editable = Craft::$app->sections->getEditableSections();

        // Get sections but not singles
        $sections = [];
        foreach ($editable as $section) {
            if ($section->type != Section::TYPE_SINGLE) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    public function getQuery($settings, $params = [])
    {
        $query = EntryElement::find();

        $criteria = array_merge([
            'status' => null,
            'sectionId' => $settings['elementGroup'][EntryElement::class]['section'],
            'typeId' => $settings['elementGroup'][EntryElement::class]['entryType'],
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
        $this->element = new EntryElement();
        $this->element->sectionId = $settings['elementGroup'][EntryElement::class]['section'];
        $this->element->typeId = $settings['elementGroup'][EntryElement::class]['entryType'];

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

        $element = EntryElement::find()
            ->status(null),
            ->andWhere(['=', $match, Db::escapeParam($value)])
            ->one();

        if ($element) {
            $this->element->newParentId = $element->id;

            return $element->id;
        }

        // Check if we should create the element. But only if title is provided (for the moment)
        if ($create && $match === 'title') {
            $element = new EntryElement();
            $element->title = $value;
            $element->sectionId = $this->element->sectionId;
            $element->typeId = $this->element->typeId;

            if (!Craft::$app->getElements()->saveElement($element)) {
                FeedMe::error(null, 'Entry error: Could not create parent entry - ' . json_encode($element->getErrors()));
            }

            FeedMe::info(null, 'Entry ' . $element->id . ' added.');

            return $element->id;
        }

        return null;
    }

    protected function parseAuthorId($feedData, $fieldInfo)
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

        if ($match === 'fullName') {
            $element = UserElement::findOne(['search' => $value, 'status' => null]);
        } else {
            $element = UserElement::find()
                ->status(null),
                ->andWhere(['=', $match, Db::escapeParam($value)])
                ->one();
        }

        if ($element) {
            return $element->id;
        }

        // Check if we should create the element. But only if email is provided (for the moment)
        if ($create && $match === 'email') {
            $element = new UserElement();
            $element->username = $value;
            $element->email = $value;

            if (!Craft::$app->getElements()->saveElement($element)) {
                FeedMe::error(null, 'Entry error: Could not create author - ' . json_encode($element->getErrors()));
            }

            FeedMe::info(null, 'Author ' . $element->id . ' added.');

            return $element->id;
        }

        return null;
    }
}

