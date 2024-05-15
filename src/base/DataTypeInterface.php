<?php

namespace craft\feedme\base;

use craft\base\ComponentInterface;

interface DataTypeInterface extends ComponentInterface
{
    // Public Methods
    // =========================================================================

    /**
     * @param $url
     * @param $settings
     * @param bool $usePrimaryElement
     * @return mixed
     */
    public function getFeed($url, $settings, bool $usePrimaryElement = true): mixed;
}
