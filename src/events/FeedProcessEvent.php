<?php

namespace verbb\feedme\events;

use craft\events\CancelableEvent;

class FeedProcessEvent extends CancelableEvent
{
    // Properties
    // =========================================================================

    public $feed;
    public $feedData;
    public $contentData;
    public $element;
}
