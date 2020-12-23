<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\feedme\base\Element;
use craft\feedme\models\ElementGroup;
use craft\feedme\Plugin;
use craft\models\Section;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read array $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class Entry extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Entry';

    /**
     * @var string
     */
    public static $class = 'craft\elements\Entry';

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
        return 'feed-me/_includes/elements/entries/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/entries/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/entries/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups()
    {
        $editable = Craft::$app->sections->getEditableSections();
        $groups = [];

        foreach ($editable as $section) {
            $groups[] = new ElementGroup([
                'id' => $section->id,
                'model' => $section,
                'isSingleton' => $section->type === Section::TYPE_SINGLE,
            ]);
        }

        return $groups;
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, $params = [])
    {
        $query = EntryElement::find()
            ->anyStatus()
            ->sectionId($settings['elementGroup'][EntryElement::class]['section'])
            ->typeId($settings['elementGroup'][EntryElement::class]['entryType'])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings)
    {
        $this->element = new EntryElement();
        $this->element->sectionId = $settings['elementGroup'][EntryElement::class]['section'];
        $this->element->typeId = $settings['elementGroup'][EntryElement::class]['entryType'];

        $section = Craft::$app->sections->getSectionById($this->element->sectionId);
        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        // Set the default site status based on the section's settings
        $enabledForSite = [];
        foreach ($section->getSiteSettings() as $siteSettings) {
            $enabledForSite[$siteSettings->siteId] = $siteSettings->enabledByDefault;
        }
        $this->element->setEnabledForSite($enabledForSite);

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

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function parseParent($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);

        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        $query = EntryElement::find()
            ->status(null)
            ->andWhere(['=', $match, $value]);

        if (isset($this->feed['siteId']) && $this->feed['siteId']) {
            $query->siteId($this->feed['siteId']);
        }

        $element = $query->one();

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
                Plugin::error('Entry error: Could not create parent - `{e}`.', ['e' => json_encode($element->getErrors())]);
            } else {
                Plugin::info('Entry `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    protected function parseAuthorId($feedData, $fieldInfo)
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');
        $node = Hash::get($fieldInfo, 'node');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            $value = $value[0];
        }

        if ($node === 'usedefault') {
            $match = 'elements.id';
        }

        if ($match === 'fullName') {
            $element = UserElement::findOne(['search' => $value, 'status' => null]);
        } else {
            $element = UserElement::find()
                ->status(null)
                ->andWhere(['=', $match, $value])
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
                Plugin::error('Entry error: Could not create author - `{e}`.', ['e' => json_encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            return $element->id;
        }

        return null;
    }
}

