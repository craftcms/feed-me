<?php

namespace craft\feedme\base;

use Cake\Utility\Hash;
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

    public function setupPaginationUrl($array, $feed)
    {
        if (!$feed->paginationNode) {
            return;
        }

        // Find the URL value in the feed
        $flatten = Hash::flatten($array, '/');
        $url = Hash::get($flatten, $feed->paginationNode);

        // Replace the mapping value with the actual URL
        $feed->paginationUrl = $url;
    }

}
