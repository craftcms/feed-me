<?php

namespace craft\feedme\events;

use yii\base\Event;

class AssetFilenameEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $field;

    /**
     * @var
     */
    public $element;

    /**
     * @var
     */
    public $fieldValue;

    /**
     * @var
     */
    public $filenames;
}
