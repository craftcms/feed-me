<?php

namespace craft\feedme\events;

use yii\base\Event;

class ElementEvent extends Event
{
    // Properties
    // =========================================================================

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
}
