<?php

namespace craft\feedme\events;

use yii\base\Event;

class RegisterFeedMeComplexFieldsEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $complexFields = [];
}
