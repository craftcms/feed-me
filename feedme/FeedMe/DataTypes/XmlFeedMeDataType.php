<?php
namespace Craft;

use Cake\Utility\Xml as Xml;

class XmlFeedMeDataType extends BaseFeedMeDataType
{
    // Public Methods
    // =========================================================================

    public function getFeed($url, $primaryElement, $settings)
    {
        if (false === ($raw_content = craft()->feedMe_data->getRawData($url))) {
            FeedMePlugin::log($settings->name . ': Unable to reach ' . $url . '. Check this is the correct URL.', LogLevel::Error, true);

            return false;
        }

        // Parse the XML string into an array
        try {
            $xml_array = Xml::build($raw_content);
            $xml_array = Xml::toArray($xml_array);
        } catch (Exception $e) {
            FeedMePlugin::log($settings->name . ': Invalid XML - ' . $e->getMessage(), LogLevel::Error, true);

            return false;
        }

        // Look for and return only the items for primary element
        if ($primaryElement && is_array($xml_array)) {
            $xml_array = craft()->feedMe_data->findPrimaryElement($primaryElement, $xml_array);
        }

        if (!is_array($xml_array)) {
            FeedMePlugin::log($settings->name . ': Invalid XML - ' . print_r($xml_array, true), LogLevel::Error, true);

            return false;
        }

        return $xml_array;
    }

}
