<?php
namespace verbb\feedme\datatypes;

use verbb\feedme\FeedMe;
use verbb\feedme\base\DataType;
use verbb\feedme\base\DataTypeInterface;

use craft\helpers\StringHelper;

use Cake\Utility\Hash;
use League\Csv\Reader;

class Csv extends DataType implements DataTypeInterface
{
    // Properties
    // =========================================================================

    public static $name = 'CSV';


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

        // Parse the CSV string - using the PHPLeague CSV package
        try {
            // Special-handling for Mac's (just in case)
            if (!ini_get('auto_detect_line_endings')) {
                ini_set('auto_detect_line_endings', '1');
            }

            // Check particularly for Windows where encoding can be off
            $data = StringHelper::convertToUtf8($data);

            $reader = Reader::createFromString($data);

            $array = [];

            // Create associative array with Row 1 header as keys
            foreach($reader->fetchAssoc(0) as $row) {
                $array[] = $row;
            }
        } catch (\Exception $e) {
            $error = 'Invalid CSV: ' . $e->getMessage();

            FeedMe::error($settings, $error);

            return ['success' => false, 'error' => $error];
        }

        // Make sure its indeed an array!
        if (!is_array($array)) {
            $error = 'Invalid CSV: ' . json_encode($array);

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
