<?php
namespace Craft;

abstract class BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getDataType()
    {
        return str_replace(array('Craft\\', 'FeedMeDataType'), array('', ''), get_class($this));
    }


    // Abstract Methods
    // =========================================================================

    abstract public function getFeed($url, $primaryElement, $settings);

}