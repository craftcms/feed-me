<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\elements\GlobalSet as GlobalSetElement;
use craft\elements\User as UserElement;
use craft\feedme\base\Element;
use craft\feedme\models\ElementGroup;
use craft\feedme\Plugin;

/**
 * Class GlobalSet
 *
 * @since 4.3.0
 */
class GlobalSet extends Element
{
    public static $name = 'Global Set';
    public static $class = GlobalSetElement::class;

    public $element;

    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/groups';
    }

    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/column';
    }

    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/map';
    }

    public function getGroups()
    {
        $editable = Craft::$app->getGlobals()->getEditableSets();
        $groups = [];

        foreach ($editable as $globalSet) {
            $groups[] = new ElementGroup([
                'id' => $globalSet->id,
                'model' => $globalSet,
                'isSingleton' => true,
            ]);
        }

        return $groups;
    }

    public function getQuery($settings, $params = [])
    {
        $query = GlobalSetElement::find()
            ->anyStatus()
            ->id($settings['elementGroup'][GlobalSetElement::class]['globalSet'])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    public function setModel($settings)
    {
        return $this->element = new GlobalSetElement();
    }
}

