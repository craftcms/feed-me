<?php

namespace craft\feedme\events;

use yii\base\Event;

class FeedDataEvent extends Event
{
    // Properties
    // =========================================================================

    public $url;
    public $response;
}
