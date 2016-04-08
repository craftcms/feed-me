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

        // Perform cleanup on raw data first
        $raw_content = preg_replace("/[\r\n]+/", " ", $raw_content);
        $json = stripslashes($raw_content);
        $json = utf8_encode($json);
        $json = StringHelper::convertToUTF8($json);

        // Parse the JSON string
        $json_array = json_decode($json, true);

        // Look for and return only the items for primary element
        $json_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $json_array);

        if (!is_array($json_array)) {
            craft()->userSession->setError(Craft::t('Invalid JSON.'));
            FeedMePlugin::log('Invalid JSON.', LogLevel::Error, true);
            
            return false;
        }

        return $json_array;
    }
}
