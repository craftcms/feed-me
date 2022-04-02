<?php

namespace craft\feedme\datatypes;

use Cake\Utility\Hash;
use Craft;
use craft\feedme\base\DataType;
use craft\feedme\base\DataTypeInterface;
use craft\feedme\Plugin;
use craft\helpers\Json as JsonHelper;
use Seld\JsonLint\JsonParser;
use yii\base\InvalidArgumentException;

class Json extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'JSON';


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getFeed($url, $settings, bool $usePrimaryElement = true): array
    {
        $feedId = Hash::get($settings, 'id');
        $response = Plugin::$plugin->data->getRawData($url, $feedId);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];

            Plugin::error($error);

            return ['success' => false, 'error' => $error];
        }

        $data = $response['data'];

        // Parse the JSON string
        try {
            $array = JsonHelper::decode($data);
        } catch (InvalidArgumentException $e) {
            // See if we can get a better error with JsonParser
            $e = (new JsonParser())->lint($data) ?: $e;
            $error = 'Invalid JSON: ' . $e->getMessage();
            Plugin::error($error);
            Craft::$app->getErrorHandler()->logException($e);
            return ['success' => false, 'error' => $error];
        }

        // Make sure it's indeed an array!
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
