<?php
namespace verbb\feedme\base;

use craft\base\ComponentInterface;

interface DataTypeInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================
    
    public function getFeed($url, $settings, $usePrimaryElement = true);

}
