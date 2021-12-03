<?php

namespace craft\feedme\events;

use yii\base\Event;

class FeedDataEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $url;

    /**
     * @var
     */
    public $feedId;

    /**
     * @var
     */
    public $response;
}
