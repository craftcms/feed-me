<?php

namespace verbb\feedme\datatypes;

use Cake\Utility\Hash;
use craft\helpers\StringHelper;
use League\Csv\Reader;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;
use verbb\feedme\FeedMe;

class Csv extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'CSV';


    // Public Methods
    // =========================================================================

    public function getFeed($url, $settings, $usePrimaryElement = true)
    {
        $feedId = Hash::get($settings, 'id');
        $response = FeedMe::$plugin->data->getRawData($url, $feedId);

        $csvColumnDelimiter = FeedMe::$plugin->service->getConfig('csvColumnDelimiter', $settings->id);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        $data = $response['data'];

        // Parse the CSV string - using the PHPLeague CSV package
        try {
            // Special-handling for Mac's (just in case)
            if (!ini_get('auto_detect_line_endings')) {
                ini_set('auto_detect_line_endings', '1');
            }

            // Check particularly for Windows where encoding can be off
            $data = StringHelper::convertToUtf8($data);

            $reader = Reader::createFromString($data);
            $reader->setDelimiter($csvColumnDelimiter);

            $array = [];

            // We need to check if the CSV provided has headers. Bit tricky in 8.x, but lets do it.
            $rows = $this->_getRows($reader);

            // Create associative array with Row 1 header as keys
            foreach ($rows as $row) {
                $filteredRow = [];

                // Additional work here to handle line-breaks in keys (CSV header) - they're not allowed
                foreach ($row as $key => $value) {
                    $newKey = preg_replace('#\r\n?#', " ", $key);
                    $filteredRow[$newKey] = $value;
                }

                // Check for empty rows - ditch them
                if ($this->_isArrayEmpty($filteredRow)) {
                    continue;
                }

                $array[] = $filteredRow;
            }
        } catch (\Exception $e) {
            $error = 'Invalid CSV: ' . $e->getMessage();

            FeedMe::error($error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid CSV: ' . json_encode($array);

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


    // Private Methods
    // =========================================================================

    private function _getRows($reader)
    {
        $array = [];

        // We try to first fetch the first row in the CSV which we figure is the headers. But if its not
        // it'll throw an error saying the first row of the CSV isn't valid, and not unique, etc.
        // So, in that case, just fail silently, and move on to the 'traditional' method which are just numbers.
        //
        // You really should provide your CSVs with headers though.

        try {
            $array = $reader->fetchAssoc(0);
        } catch (\Throwable $e) {
        }

        try {
            if (!$array) {
                $array = $reader->fetch();
            }
        } catch (\Throwable $e) {
        }

        // Support league/csv v9 syntax
        try {
            if (!$array) {
                $reader->setHeaderOffset(0);
                $array = $reader->getRecords();
            }
        } catch (\Throwable $e) {
        }

        return $array;
    }

    private function _isArrayEmpty($array)
    {
        foreach ($array as $key => $val) {
            if (trim($val) !== '') {
                return false;
            }
        }

        return true;
    }
}
