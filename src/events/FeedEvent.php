<?php

namespace semabit\feedme\events;

use yii\base\Event;

class FeedEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public mixed $feed = null;

    /**
     * @var bool
     */
    public bool $isNew = false;
}
