<?php
namespace Craft;

abstract class BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getDisplayName()
    {
        return StringHelper::toUpperCase($this->getDataType());
    }

    public function getDataType()
    {
        // The data type is the short name of the class minus the 'FeedMeDataType' suffix.
        $shortName = substr(strrchr(get_class($this), '\\'), 1);
        return str_replace('FeedMeDataType', '', $shortName);
    }


    // Abstract Methods
    // =========================================================================

    abstract public function getFeed($url, $primaryElement, $settings);

}