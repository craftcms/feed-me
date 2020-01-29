<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\elements\Tag as TagElement;
use craft\feedme\base\Element;

class Tag extends Element
{
    // Properties
    // =========================================================================

    public static $name = 'Tag';
    public static $class = 'craft\elements\Tag';

    public $element;


    // Templates
    // =========================================================================

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/tag/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/tag/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/tag/map';
    }


    // Public Methods
    // =========================================================================

    public function getGroups()
    {
        return Craft::$app->tags->getAllTagGroups();
    }

    public function getQuery($settings, $params = [])
    {
        $query = TagElement::find()
            ->anyStatus()
            ->groupId($settings['elementGroup'][TagElement::class])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

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
