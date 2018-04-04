<?php
namespace verbb\feedme\events;

use yii\base\Event;

class FeedEvent extends Event
{
    // Properties
    // =========================================================================

    public $feed;

    public $isNew = false;
}
