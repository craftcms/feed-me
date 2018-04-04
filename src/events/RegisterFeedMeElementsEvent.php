<?php
namespace verbb\feedme\events;

use yii\base\Event;

class RegisterFeedMeElementsEvent extends Event
{
    // Properties
    // =========================================================================

    public $elements = [];
}
