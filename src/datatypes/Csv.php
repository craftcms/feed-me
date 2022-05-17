<?php

namespace craft\feedme\datatypes;

use Cake\Utility\Hash;
use Craft;
use craft\feedme\base\DataType;
use craft\feedme\base\DataTypeInterface;
use craft\feedme\Plugin;
use craft\helpers\StringHelper;
use Exception;
use League\Csv\Reader;
use League\Csv\Statement;
use Throwable;

class Csv extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public static string $name = 'CSV';


    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getFeed($url, $settings, bool $usePrimaryElement = true): array
    {
        $feedId = Hash::get($settings, 'id');
        $response = Plugin::$plugin->data->getRawData($url, $feedId);

        $csvColumnDelimiter = Plugin::$plugin->service->getConfig('csvColumnDelimiter', $settings->id);

        if (!$response['success']) {
            $error = 'Unable to reach ' . $url . '. Message: ' . $response['error'];

            Plugin::error($error);

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

            // We need to check if the CSV provided has headers. This is a bit tricky in 8.x, but let's do it.
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
        } catch (Exception $e) {
            $error = 'Invalid CSV: ' . $e->getMessage();

            Plugin::error($error);
            Craft::$app->getErrorHandler()->logException($e);

            return ['success' => false, 'error' => $error];
        }

        // Make sure it's indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid CSV: ' . \craft\helpers\Json::encode($array);

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


    // Private Methods
    // =========================================================================

    /**
     * @param $reader
     * @return array
     * @throws \League\Csv\Exception
     */
    private function _getRows($reader): mixed
    {
        // We try to first fetch the first row in the CSV which we figure is the headers. But if it's not
        // it'll throw an error saying the first row of the CSV isn't valid, and not unique, etc.
        // So, in that case, just fail silently, and move on to the 'traditional' method which are just numbers.
        //
        // You really should provide your CSVs with headers though.

        // Support for league/csv v8 with a header
        try {
            return $reader->fetchAssoc(0);
        } catch (Throwable $e) {
        }

        // Support for league/csv v8 without a header
        try {
            return $reader->fetch();
        } catch (Throwable $e) {
        }

        $stmt = Statement::create();

        // Support for league/csv v9 with a header
        try {
            $reader->setHeaderOffset(0);

            return $stmt->process($reader);
        } catch (Throwable $e) {
        }

        // Support for league/csv v9 without a header
        $reader->setHeaderOffset(null);

        return $stmt->process($reader);
    }

    /**
     * @param $array
     * @return bool
     */
    private function _isArrayEmpty($array): bool
    {
        foreach ($array as $val) {
            if (is_string($val)) {
                if (trim($val) !== '') {
                    return false;
                }
            } elseif ($val) {
                return false;
            }
        }

        return true;
    }
}
