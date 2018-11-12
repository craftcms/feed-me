<?php
namespace verbb\feedme\datatypes;

use verbb\feedme\FeedMe;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;

use Cake\Utility\Hash;
use craft\helpers\Json as JsonHelper;

class Json extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'JSON';


    // Public Methods
    // =========================================================================

    public function getFeed($url, $settings, $usePrimaryElement = true)
    {
        $response = FeedMe::$plugin->data->getRawData($url);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];
            
            FeedMe::error($error);
            
            return ['success' => false, 'error' => $error];
        }

        $data = $response['data'];

        // Parse the JSON string - using Yii's built-in cleanup
        try {
            $array = JsonHelper::decode($data, true);
        } catch (\Exception $e) {
            $error = 'Invalid JSON: ' . $e->getMessage();

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid JSON: ' . json_last_error_msg();

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
