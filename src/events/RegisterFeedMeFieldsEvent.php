<?php

namespace craft\feedme\events;

use yii\base\Event;

class RegisterFeedMeFieldsEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $fields = [];

    /**
     * @var array
     * @sinde 5.13.0
     */
    public array $nativeFields = [];
}
