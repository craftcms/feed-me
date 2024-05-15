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
     * @var int|null The ID of the group, which corresponds to the group option’s value
     */
    public ?int $id = null;

    /**
     * @var mixed The group model
     */
    public mixed $model;

    /**
     * @var bool Whether the group contains a singleton element
     */
    public bool $isSingleton = false;
}
