<?php

namespace craft\feedme\elements;

use Cake\Utility\Hash;
use Craft;
use craft\base\ElementInterface;
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
    public static string $name = 'Global Set';

    /**
     * @var string
     */
    public static string $class = GlobalSetElement::class;

    /**
     * @inheritDoc
     */
    public function getGroupsTemplate(): string
    {
        return 'feed-me/_includes/elements/global-sets/groups';
    }

    /**
     * @inheritDoc
     */
    public function getColumnTemplate(): string
    {
        return 'feed-me/_includes/elements/global-sets/column';
    }

    /**
     * @inheritDoc
     */
    public function getMappingTemplate(): string
    {
        return 'feed-me/_includes/elements/global-sets/map';
    }

    /**
     * @inheritDoc
     */
    public function getGroups(): array
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
    public function getQuery($settings, array $params = []): mixed
    {
        $query = GlobalSetElement::find()
            ->status(null)
            ->id($settings['elementGroup'][GlobalSetElement::class]['globalSet'])
            ->siteId(Hash::get($settings, 'siteId') ?: Craft::$app->getSites()->getPrimarySite()->id);
        Craft::configure($query, $params);
        return $query;
    }

    /**
     * @inheritDoc
     */
    public function setModel($settings): ElementInterface
    {
        return $this->element = new GlobalSetElement();
    }
}
