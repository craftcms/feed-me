<?php
namespace verbb\feedme\elements;

use verbb\feedme\FeedMe;
use verbb\feedme\base\Element;
use verbb\feedme\base\ElementInterface;

use Craft;
use craft\elements\Tag as TagElement;
use craft\helpers\Db;

use Cake\Utility\Hash;

class Tag extends Element implements ElementInterface
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
        $query = TagElement::find();

        $criteria = array_merge([
            'status' => null,
            'groupId' => $settings['elementGroup'][TagElement::class],
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
        $this->element = new TagElement();
        $this->element->groupId = $settings['elementGroup'][TagElement::class];

        $siteId = Hash::get($settings, 'siteId');

        if ($siteId) {
            $this->element->siteId = $siteId;
        }

        return $this->element;
    }

}
