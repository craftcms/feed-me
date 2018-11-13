<?php
namespace verbb\feedme\datatypes;

use verbb\feedme\FeedMe;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;

use Craft;

use Cake\Utility\Hash;
use craft\helpers\Json as JsonHelper;

class GoogleSheet extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'Google Sheet';


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

        try {
            $content = JsonHelper::decode($data, true);

            $headers = array_shift($content['values']);
            $rows = $content['values'];

            $array = [];

            foreach ($rows as $i => $row) {
                foreach ($row as $j => $column) {
                    $key = $headers[$j];

                    $array[$i][$key] = $column;
                }
            }
        } catch (\Exception $e) {
            $error = 'Invalid data: ' . $e->getMessage();

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid data: ' . json_encode($array);

            FeedMe::error($error);

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
