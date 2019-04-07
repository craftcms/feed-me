<?php

namespace craft\feedme\datatypes;

use Cake\Utility\Hash;
use craft\feedme\base\DataType;
use craft\feedme\base\DataTypeInterface;
use craft\feedme\Plugin;
use Seld\JsonLint\JsonParser;

class Json extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'JSON';


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

        // Parse the JSON string - using Yii's built-in cleanup
        try {
            // Try to parse the JSON first, no real useful error handling for stock JSON
            $parser = new JsonParser();
            $parseErrors = $parser->lint($data);

            if ($parseErrors) {
                throw $parseErrors;
            }

            $array = json_decode($data, true);
        } catch (\Throwable $e) {
            $error = 'Invalid JSON: ' . $e->getMessage();

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid JSON: ' . json_last_error_msg();

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
