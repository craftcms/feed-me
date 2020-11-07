<?php

namespace craft\feedme\models;

use Cake\Utility\Hash;
use Craft;
use craft\base\Model;
use craft\elements\Entry as EntryElement;
use craft\elements\User as UserElement;
use craft\feedme\base\Element;
use craft\feedme\Plugin;
use craft\models\Section;

/**
 * Class ElementGroup
 *
 * @since 4.3.0
 */
class ElementGroup extends Model
{
    /**
     * @var string The ID of the group, which corresponds to the group option’s value
     */
    public $id;

    /**
     * @var mixed The group model
     */
    public $model;

    /**
     * @var bool Whether the group contains a singleton element
     */
    public $isSingleton = false;
}
