<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\elements\Tag as TagElement;
use craft\feedme\base\Element;

/**
 *
 * @property-read string $mappingTemplate
 * @property-read mixed $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class Tag extends Element
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Tag';

    /**
     * @var string
     */
    public static $class = 'craft\elements\Tag';

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
        return 'feed-me/_includes/elements/tag/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/tag/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/tag/map';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getGroups()
    {
        return Craft::$app->tags->getAllTagGroups();
    }

    /**
     * @inheritDoc
     */
    public function getQuery($settings, $params = [])
    {
        $query = TagElement::find()
            ->anyStatus()
            ->groupId($settings['elementGroup'][TagElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings)
    {
        $this->element = new TagElement();
        $this->element->groupId = $settings['elementGroup'][TagElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }
}
