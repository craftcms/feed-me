<?php
namespace Craft;

class JsonFeedMeDataType extends BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement)
    {
        if (false === ($raw_content = craft()->feedMe_data->getRawData($url))) {
            FeedMePlugin::log('Unable to reach ' . $url . '. Check this is the correct URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the JSON string - using Yii's built-in cleanup
        $json_array = JsonHelper::decode($raw_content, true);

        // Look for and return only the items for primary element
        if ($primaryElement && is_array($json_array)) {
            $json_array = craft()->feedMe_data->findPrimaryElement($primaryElement, $json_array);
        }

        if (!is_array($json_array)) {
            FeedMePlugin::log('Invalid JSON. - ' . json_last_error_msg(), LogLevel::Error, true);
            
            return false;
        }

        return $json_array;
    }
}
