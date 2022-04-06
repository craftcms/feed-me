<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\elements\GlobalSet as GlobalSetElement;
use craft\feedme\base\Element;
use craft\feedme\models\ElementGroup;

/**
 * Class GlobalSet
 *
 * @since 4.3.0
 *
 * @property-read string $mappingTemplate
 * @property-read array $groups
 * @property-write mixed $model
 * @property-read string $groupsTemplate
 * @property-read string $columnTemplate
 */
class GlobalSet extends Element
{
    /**
     * @var string
     */
    public static $name = 'Global Set';

    /**
     * @var string
     */
    public static $class = GlobalSetElement::class;

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate()
    {
        return 'feed-me/_includes/elements/global-sets/map';
    }

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function getQuery($settings, $params = [])
    {
        $query = GlobalSetElement::find()
            ->anyStatus()
            ->id($settings['elementGroup'][GlobalSetElement::class]['globalSet'])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings)
    {
        return $this->element = new GlobalSetElement();
    }
}
