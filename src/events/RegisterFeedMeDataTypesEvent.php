<?php

namespace craft\feedme\events;

use yii\base\Event;

class RegisterFeedMeDataTypesEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $dataTypes = [];
}
