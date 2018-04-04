<?php
namespace verbb\feedme\base;

use Craft;
use craft\base\Component;

abstract class DataType extends Component
{
    // Public
    // =========================================================================

    public function getName()
    {
        return $this::$name;
    }

    public function getClass()
    {
        return get_class($this);
    }

}