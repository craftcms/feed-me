<?php

namespace craft\feedme\events;

use craft\base\ElementInterface;
use yii\base\Event;

/**
 * Compare content event class.
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 5.12.0
 */
class CompareContentEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    public array $content;

    /**
     * @var ElementInterface
     */
    public ElementInterface $element;

    /**
     * @var string
     */
    public string $handle;

    /**
     * @var mixed
     */
    public mixed $existingValue;

    /**
     * @var mixed
     */
    public mixed $newValue;
}
