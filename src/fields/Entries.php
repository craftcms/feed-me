<?php
namespace craft\feedme\fields;

use Cake\Utility\Hash;
use Craft;
use craft\base\Element as BaseElement;
use craft\elements\Entry as EntryElement;
use craft\feedme\base\Field;
use craft\feedme\base\FieldInterface;
use craft\feedme\Plugin;
use craft\helpers\Db;
use craft\helpers\StringHelper;

/**
 *
 * @property-read string $mappingTemplate
 */
class Entries extends Field implements FieldInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Entries';

    /**
     * @var string
     */
    public static $class = 'craft\fields\Entries';

    /**
     * @var string
     */
    public static $elementType = 'craft\elements\Entry';

    // Templates
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/fields/entries';
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
        $targetSiteId = Hash::get($this->field, 'settings.targetSiteId');
        $feedSiteId = Hash::get($this->feed, 'siteId');
        $match = Hash::get($this->fieldInfo, 'options.match', 'title');
        $create = Hash::get($this->fieldInfo, 'options.create');
        $fields = Hash::get($this->fieldInfo, 'fields');
        $node = Hash::get($this->fieldInfo, 'node');

        $sectionIds = [];

        if (is_array($sources)) {
            foreach ($sources as $source) {
                // When singles is selected as the only option to search in, it doesn't contain any ids...
                if ($source == 'singles') {
                    foreach (Craft::$app->sections->getAllSections() as $section) {
                        $sectionIds[] = ($section->type == 'single') ? $section->id : '';
                    }
                } else {
                    list(, $uid) = explode(':', $source);
                    $sectionIds[] = Db::idByUid('{{%sections}}', $uid);
                }
            }
        } elseif ($sources === '*') {
            $sectionIds = null;
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

            $query = EntryElement::find();

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

            // Because we can match on element attributes and custom fields, AND we're directly using SQL
            // queries in our `where` below, we need to check if we need a prefix for custom fields accessing
            // the content table.
            $columnName = $match;

            if (Craft::$app->getFields()->getFieldByHandle($match)) {
                $columnName = Craft::$app->getFields()->oldFieldColumnPrefix . $match;
            }

            $criteria['status'] = null;
            $criteria['enabledForSite'] = false;
            $criteria['sectionId'] = $sectionIds;
            $criteria['limit'] = $limit;
            $criteria['where'] = ['=', $columnName, $dataValue];

            Craft::configure($query, $criteria);

            Plugin::info('Search for existing entry with query `{i}`', ['i' => json_encode($criteria)]);

            $ids = $query->ids();

            $foundElements = array_merge($foundElements, $ids);

            Plugin::info('Found `{i}` existing entries: `{j}`', ['i' => count($foundElements), 'j' => json_encode($foundElements)]);

            // Check if we should create the element.
            if (count($ids) == 0) {
                if ($create) {
                    $foundElements[] = $this->_createElement($dataValue, $match);
                }
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
     * @param string $match
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @return int|null
     */
    private function _createElement($dataValue, $match)
    {
        $sectionId = Hash::get($this->fieldInfo, 'options.group.sectionId');
        $typeId = Hash::get($this->fieldInfo, 'options.group.typeId');

        // Bit of backwards-compatibility here, if not explicitly set, grab the first globally
        if (!$sectionId) {
            $sectionId = Craft::$app->sections->getAllSectionIds()[0];
        }

        if (!$typeId) {
            $typeId = Craft::$app->sections->getEntryTypesBySectionId($sectionId)[0]->id;
        }

        $element = new EntryElement();
        $element->sectionId = $sectionId;
        $element->typeId = $typeId;

        $siteId = Hash::get($this->feed, 'siteId');
        $section = Craft::$app->sections->getSectionById($element->sectionId);

        if ($match === 'title') {
            $element->title = $dataValue;

            if ($siteId) {
                $element->siteId = $siteId;

                // Set the default site status based on the section's settings
                foreach ($section->getSiteSettings() as $siteSettings) {
                    if ($siteSettings->siteId == $siteId) {
                        $element->enabledForSite = $siteSettings->enabledByDefault;
                        break;
                    }
                }
            } else {
                // Set the default entry status based on the section's settings
                foreach ($section->getSiteSettings() as $siteSettings) {
                    if (!$siteSettings->enabledByDefault) {
                        $element->enabled = false;
                    }

                    break;
                }
            }
        } else {
            // If the new element has no title: Create a random title and disable the element.
            $randomString = '__feed-me__' . strtolower(StringHelper::randomString(10));
            $entryType = $element->getType();

            // If the element has no title field, we only set a random slug.
            // Otherwise we would not be able to save the element.
            if ($entryType->hasTitleField) {
                $element->title = $randomString;
            } else {
                $element->slug = $randomString;
            }
            $element->setFieldValue(str_replace('field_', '', $match), $dataValue);
            $element->enabled = false;
        }

        $element->setScenario(BaseElement::SCENARIO_ESSENTIALS);

        if (!Craft::$app->getElements()->saveElement($element)) {
            Plugin::error('`{handle}` - Entry error: Could not create - `{e}`.', ['e' => json_encode($element->getErrors()), 'handle' => $this->field->handle]);
        } else {
            Plugin::info('`{handle}` - Entry `#{id}` added.', ['id' => $element->id, 'handle' => $this->field->handle]);
        }

        return $element->id;
    }
}
