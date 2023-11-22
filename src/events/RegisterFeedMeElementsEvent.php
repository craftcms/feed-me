<?php

namespace semabit\feedme\events;

use yii\base\Event;

class RegisterFeedMeElementsEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $elements = [];
}
