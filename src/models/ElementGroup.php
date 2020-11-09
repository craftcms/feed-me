<?php

namespace craft\feedme\models;

use craft\base\Model;

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
