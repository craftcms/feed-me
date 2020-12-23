<?php

namespace craft\feedme\events;

use yii\base\Event;

class FeedEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $feed;

    /**
     * @var bool
     */
    public $isNew = false;
}
