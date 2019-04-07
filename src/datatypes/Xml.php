<?php

namespace verbb\feedme\datatypes;

use Cake\Utility\Hash;
use Cake\Utility\Xml as XmlParser;
use Craft;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;
use verbb\feedme\Plugin;

class Xml extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'XML';


    // Public Methods
    // =========================================================================

    public function getFeed($url, $settings, $usePrimaryElement = true)
    {
        $feedId = Hash::get($settings, 'id');
        $response = Plugin::$plugin->data->getRawData($url, $feedId);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        $data = $response['data'];

        // Parse the XML string into an array
        try {
            // Allow parsing errors to be caught
            libxml_use_internal_errors(true);

            $array = XmlParser::build($data);
            $array = XmlParser::toArray($array);
        } catch (\Exception $e) {
            // Get a more useful error from parsing - if available
            if ($parseErrors = libxml_get_errors()) {
                $error = Craft::t('feed-me', 'Invalid XML: {e}: Line #{l}.', ['e' => $parseErrors[0]->message, 'l' => $parseErrors[0]->line]);
            } else {
                $error = Craft::t('feed-me', 'Invalid XML: {e}.', ['e' => $e->getMessage()]);
            }

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid XML: ' . json_encode($array);

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        // If using pagination, set it up here - we need to do this before messing around with the primary element
        $this->setupPaginationUrl($array, $settings);

        // Look for and return only the items for primary element
        $primaryElement = Hash::get($settings, 'primaryElement');

        if ($primaryElement && $usePrimaryElement) {
            $array = Plugin::$plugin->data->findPrimaryElement($primaryElement, $array);
        }

        return ['success' => true, 'data' => $array];
    }

}
