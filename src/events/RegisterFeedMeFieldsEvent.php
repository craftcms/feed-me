<?php

namespace semabit\feedme\events;

use yii\base\Event;

class RegisterFeedMeFieldsEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $fields = [];
}
