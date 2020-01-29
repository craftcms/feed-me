<?php

namespace craft\feedme\events;

use yii\base\Event;

class ElementEvent extends Event
{
    // Properties
    // =========================================================================

    public $feedData;
    public $fieldHandle;
    public $fieldInfo;
    public $parsedValue;
}
