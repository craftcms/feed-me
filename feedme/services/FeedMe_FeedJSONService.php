<?php
namespace Craft;

class FeedMe_FeedJSONService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement) {
        if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
            craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
            FeedMePlugin::log('Unable to parse Feed URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the JSON string - using Yii's built-in cleanup
        $json_array = JsonHelper::decode($raw_content, true);

        // Look for and return only the items for primary element
        $json_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $json_array);

        if (!is_array($json_array)) {
            $error = 'Invalid JSON. - ' . json_last_error_msg();

            craft()->userSession->setError(Craft::t($error));
            FeedMePlugin::log($error, LogLevel::Error, true);
            
            return false;
        }

        return $json_array;
    }
}
