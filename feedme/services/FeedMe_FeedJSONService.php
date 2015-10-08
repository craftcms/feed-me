<?php
namespace Craft;

class FeedMe_FeedJSONService extends BaseApplicationComponent
{
    public function getFeed($url, $primaryElement) {
        if (false === ($raw_content = craft()->feedMe_feed->getRawData($url))) {
            craft()->userSession->setError(Craft::t('Unable to parse Feed URL.'));
            return false;
        }

        // Parse the JSON string
        $json_array = json_decode($raw_content, true);

        // Look for and return only the items for primary element
        $json_array = craft()->feedMe_feed->findPrimaryElement($primaryElement, $json_array);

        if (!is_array($json_array)) {
            craft()->userSession->setError(Craft::t('Invalid JSON.'));
            return false;
        }

        return $json_array;
    }
}
