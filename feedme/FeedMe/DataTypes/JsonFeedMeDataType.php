<?php
namespace Craft;

class JsonFeedMeDataType extends BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement, $settings)
    {   
        // Check for when calling via templates (there's no feed model)
        $name = ($settings) ? $settings->name . ': ' : '';

        if (false === ($raw_content = craft()->feedMe_data->getRawData($url))) {
            FeedMePlugin::log($name . 'Unable to reach ' . $url . '. Check this is the correct URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the JSON string - using Yii's built-in cleanup
        try {
            $json_array = JsonHelper::decode($raw_content, true);
        } catch (Exception $e) {
            FeedMePlugin::log($name . 'Invalid JSON - ' . $e->getMessage(), LogLevel::Error, true);

            return false;
        }

        // Look for and return only the items for primary element
        if ($primaryElement && is_array($json_array)) {
            $json_array = craft()->feedMe_data->findPrimaryElement($primaryElement, $json_array);
        }

        if (!is_array($json_array)) {
            FeedMePlugin::log($name . 'Invalid JSON. - ' . json_last_error_msg(), LogLevel::Error, true);
            
            return false;
        }

        return $json_array;
    }
}
