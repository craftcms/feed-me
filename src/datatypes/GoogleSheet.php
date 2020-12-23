<?php

namespace craft\feedme\datatypes;

use Cake\Utility\Hash;
use craft\feedme\base\DataType;
use craft\feedme\base\DataTypeInterface;
use craft\feedme\Plugin;
use craft\helpers\Json as JsonHelper;

class GoogleSheet extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static $name = 'Google Sheet';


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
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

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid data: ' . json_encode($array);

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Look for and return only the items for primary element
        $primaryElement = Hash::get($settings, 'primaryElement');

        if ($primaryElement && $usePrimaryElement) {
            $array = Plugin::$plugin->data->findPrimaryElement($primaryElement, $array);
        }

        return ['success' => true, 'data' => $array];
    }
}
