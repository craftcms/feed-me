<?php

namespace craft\feedme\events;

use yii\base\Event;

class RegisterFeedMeDataTypesEvent extends Event
{
    // Properties
    // =========================================================================

    public $dataTypes = [];
}
