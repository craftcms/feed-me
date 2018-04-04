<?php
namespace verbb\feedme\events;

use yii\base\Event;

class FeedProcessEvent extends Event
{
    // Properties
    // =========================================================================

    public $feed;

    public $feedData;

    public $contentData;

    public $element;
}
