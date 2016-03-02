<?php
namespace Craft;

class FeedMe_FeedJSONService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement) {
        if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
            craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
            return false;
        }

        // Perform cleanup on raw data first
        $raw_content = preg_replace("/[\r\n]+/", " ", $raw_content);
        $json = utf8_encode($raw_content);

        // Parse the JSON string
        $json_array = json_decode($json, true);

        // Look for and return only the items for primary element
        $json_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $json_array);

        if (!is_array($json_array)) {
            craft()->userSession->setError(Craft::t('Invalid JSON.'));
            return false;
        }

        return $json_array;
    }
}
