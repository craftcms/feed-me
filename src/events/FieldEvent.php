<?php

namespace craft\feedme\events;

use yii\base\Event;

class FieldEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $feed;

    /**
     * @var
     */
    public $feedData;

    /**
     * @var
     */
    public $fieldHandle;

    /**
     * @var
     */
    public $fieldInfo;

    /**
     * @var
     */
    public $parsedValue;

    /**
     * @var
     */
    public $element;
}
