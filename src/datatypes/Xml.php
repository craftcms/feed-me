<?php
namespace verbb\feedme\datatypes;

use verbb\feedme\FeedMe;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;

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
        $response = FeedMe::$plugin->data->getRawData($url);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];
            
            FeedMe::error($settings, $error);
            
            return ['success' => false, 'error' => $error];
        }

        $data = $response['data'];

        // Parse the XML string into an array
        try {
            $array = XmlParser::build($data);
            $array = XmlParser::toArray($array);
        } catch (\Exception $e) {
            $error = 'Invalid XML: ' . $e->getMessage();

            FeedMe::error($settings, $error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid XML: ' . json_encode($array);

            FeedMe::error($settings, $error);

            return ['success' => false, 'error' => $error];
        }

        // Look for and return only the items for primary element
        $primaryElement = Hash::get($settings, 'primaryElement');

        if ($primaryElement && $usePrimaryElement) {
            $array = FeedMe::$plugin->data->findPrimaryElement($primaryElement, $array);
        }

        return ['success' => true, 'data' => $array];
    }

}
