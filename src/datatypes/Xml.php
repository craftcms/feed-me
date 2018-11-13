<?php
namespace verbb\feedme\datatypes;

use verbb\feedme\FeedMe;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;

use Craft;

use Cake\Utility\Hash;
use Cake\Utility\Xml as XmlParser;

class Xml extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'XML';


    // Public Methods
    // =========================================================================

    public function getFeed($url, $settings, $usePrimaryElement = true)
    {
        $response = FeedMe::$plugin->data->getRawData($url, $settings->id);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];
            
            FeedMe::error($error);
            
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

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid XML: ' . json_encode($array);

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        // If using pagination, set it up here - we need to do this before messing around with the primary element
        $this->setupPaginationUrl($array, $settings);

        // Look for and return only the items for primary element
        $primaryElement = Hash::get($settings, 'primaryElement');

        if ($primaryElement && $usePrimaryElement) {
            $array = FeedMe::$plugin->data->findPrimaryElement($primaryElement, $array);
        }

        return ['success' => true, 'data' => $array];
    }

}
