<?php

namespace craft\feedme\events;

use craft\events\CancelableEvent;

class FeedProcessEvent extends CancelableEvent
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public mixed $feed = null;

    /**
     * @var
     */
    public mixed $feedData = null;

    /**
     * @var
     */
    public mixed $contentData = null;

    /**
     * @var
     */
    public mixed $element = null;
}
