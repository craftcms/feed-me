<?php

namespace craft\feedme\events;

use yii\base\Event;

class FieldEvent extends Event
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
    public mixed $fieldHandle = null;

    /**
     * @var
     */
    public mixed $fieldInfo = null;

    /**
     * @var
     */
    public mixed $parsedValue = null;

    /**
     * @var
     */
    public mixed $element = null;
}
