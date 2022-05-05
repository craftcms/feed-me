<?php

namespace craft\feedme\events;

use yii\base\Event;

class FeedDataEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var string|null
     */
    public ?string $url = null;

    /**
     * @var int|null
     */
    public ?int $feedId = null;

    /**
     * @var
     */
    public mixed $response = null;
}
