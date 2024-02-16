<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Carbon\Carbon;
use Craft;
use craft\base\ElementInterface;
use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\errors\ElementNotFoundException;
use craft\feedme\base\Element;
use craft\feedme\helpers\DataHelper;
use craft\feedme\models\ElementGroup;
use craft\feedme\Plugin;
use craft\helpers\ElementHelper;
use craft\helpers\Json;
use craft\models\Section;
use DateTime;
use Throwable;
use yii\base\Exception;

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
    public static string $name = 'Entry';

    /**
     * @var string
     */
    public static string $class = EntryElement::class;

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/entries/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/entries/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/entries/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        $editable = Craft::$app->getEntries()->getEditableSections();
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
    public function getQuery($settings, array $params = []): mixed
    {
        $targetSiteId = Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id;
        if ($this->element !== null) {
            $section = $this->element->getSection();
        }

        $query = EntryElement::find()
            ->status(null)
            ->sectionId($settings['elementGroup'][EntryElement::class]['section'])
            ->typeId($settings['elementGroup'][EntryElement::class]['entryType']);

        if (isset($section) && $section->propagationMethod === \craft\enums\PropagationMethod::Custom) {
            $query->site('*')
                ->preferSites([$targetSiteId])
                ->unique();
        } else {
            $query->siteId($targetSiteId);
        }

        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
    {
        $this->element = new EntryElement();
        $this->element->sectionId = $settings['elementGroup'][EntryElement::class]['section'];
        $this->element->typeId = $settings['elementGroup'][EntryElement::class]['entryType'];

        $section = Craft::$app->getEntries()->getSectionById($this->element->sectionId);
        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        // Set the default site status based on the section's settings
        $enabledForSite = [];
        foreach ($section->getSiteSettings() as $siteSettings) {
            if (
                $section->propagationMethod !== \craft\enums\PropagationMethod::Custom ||
                $siteSettings->siteId == $siteId
            ) {
                $enabledForSite[$siteSettings->siteId] = $siteSettings->enabledByDefault;
            }
        }
        $this->element->setEnabledForSite($enabledForSite);

        return $this->element;
    }

    /**
     * Checks if $existingElement should be propagated to the target site.
     *
     * @param $existingElement
     * @param array $feed
     * @return ElementInterface|null
     * @throws Exception
     * @throws \craft\errors\SiteNotFoundException
     * @throws \craft\errors\UnsupportedSiteException
     * @since 5.1.3
     */
    public function checkPropagation($existingElement, array $feed)
    {
        $targetSiteId = Hash::get($feed, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id;

        // Did the entry come back in a different site?
        if ($existingElement->siteId != $targetSiteId) {
            // Skip it if its section doesn't use the `custom` propagation method
            if ($existingElement->getSection()->propagationMethod !== \craft\enums\PropagationMethod::Custom) {
                return $existingElement;
            }

            // Give the entry a status for the import's target site
            // (This is how the `custom` propagation method knows which sites the entry should support.)
            $siteStatuses = ElementHelper::siteStatusesForElement($existingElement);
            $siteStatuses[$targetSiteId] = $existingElement->getEnabledForSite();
            $existingElement->setEnabledForSite($siteStatuses);

            // Propagate the entry, and swap $entry with the propagated copy
            return Craft::$app->getElements()->propagateElement($existingElement, $targetSiteId);
        }

        return $existingElement;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|Carbon|DateTime|false|string|null
     * @throws \Exception
     */
    protected function parsePostDate($feedData, $fieldInfo): DateTime|bool|array|Carbon|string|null
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|Carbon|DateTime|false|string|null
     * @throws \Exception
     */
    protected function parseExpiryDate($feedData, $fieldInfo): DateTime|bool|array|Carbon|string|null
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $formatting = Hash::get($fieldInfo, 'options.match');

        return $this->parseDateAttribute($value, $formatting);
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return int|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    protected function parseParent($feedData, $fieldInfo): ?int
    {
        $value = $this->fetchSimpleValue($feedData, $fieldInfo);
        $default = DataHelper::fetchDefaultArrayValue($fieldInfo);

        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');
        $node = Hash::get($fieldInfo, 'node');

        // Element lookups must have a value to match against
        if ($value === null || $value === '') {
            return null;
        }

        if ($node === 'usedefault' || $value === $default) {
            $match = 'elements.id';
        }

        if (is_array($value)) {
            $value = $value[0];
        }

        $query = EntryElement::find()
            ->status(null)
            ->andWhere(['=', $match, $value]);

        if (isset($this->feed['siteId']) && $this->feed['siteId']) {
            $query->siteId($this->feed['siteId']);
        }

        // fix for https://github.com/craftcms/feed-me/issues/1154#issuecomment-1429622276
        if (!empty($this->element->sectionId)) {
            $query->sectionId($this->element->sectionId);
        }

        $element = $query->one();

        if ($element) {
            $this->element->setParentId($element->id);

            return $element->id;
        }

        // Check if we should create the element. But only if title is provided (for the moment)
        if ($create && $match === 'title') {
            $element = new EntryElement();
            $element->title = $value;
            $element->sectionId = $this->element->sectionId;
            $element->typeId = $this->element->typeId;

            if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
                Plugin::error('Entry error: Could not create parent - `{e}`.', ['e' => Json::encode($element->getErrors())]);
            } else {
                Plugin::info('Entry `#{id}` added.', ['id' => $element->id]);
                $this->element->parentId = $element->id;
            }

            return $element->id;
        }

        // use the default value if it's provided and none of the above worked
        // https://github.com/craftcms/feed-me/issues/1154
        if (!empty($default)) {
            $this->element->parentId = $default[0];

            return $default[0];
        }

        return null;
    }

    /**
     * @param $feedData
     * @param $fieldInfo
     * @return array|null
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    protected function parseAuthorIds($feedData, $fieldInfo): ?array
    {
        $values = $this->fetchArrayValue($feedData, $fieldInfo);
        $default = DataHelper::fetchDefaultArrayValue($fieldInfo);

        $match = Hash::get($fieldInfo, 'options.match');
        $create = Hash::get($fieldInfo, 'options.create');
        $node = Hash::get($fieldInfo, 'node');

        // Element lookups must have a value to match against
        if (empty($values)) {
            return null;
        }

        if ($node === 'usedefault' || $values === $default) {
            $match = 'elements.id';
        }

        $matchedIds = null;
        foreach ($values as $value) {
            if ($match === 'fullName') {
                $element = UserElement::findOne(['search' => $value, 'status' => null]);
            } else {
                $element = UserElement::find()
                    ->status(null)
                    ->andWhere(['=', $match, $value])
                    ->one();
            }

            if ($element) {
                $matchedIds[] = $element->id;
            }
        }

        // Check if we should create the element. But only if email is provided (for the moment)
        if ($create && $match === 'email') {
            $element = new UserElement();
            $element->username = $value;
            $element->email = $value;

            if (!Craft::$app->getElements()->saveElement($element, true, true, Hash::get($this->feed, 'updateSearchIndexes'))) {
                Plugin::error('Entry error: Could not create author - `{e}`.', ['e' => Json::encode($element->getErrors())]);
            } else {
                Plugin::info('Author `#{id}` added.', ['id' => $element->id]);
            }

            $matchedIds[] = $element->id;
        }

        return $matchedIds;
    }
}
