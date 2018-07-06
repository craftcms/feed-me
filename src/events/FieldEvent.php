<?php
namespace verbb\feedme\events;

use yii\base\Event;

class FieldEvent extends Event
{
    // Properties
    // =========================================================================
    
    public $feed;

    public $feedData;

    public $fieldHandle;

    public $fieldInfo;

    public $parsedValue;

    public $element;
}
