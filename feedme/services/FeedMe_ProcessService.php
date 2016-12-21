<?php
namespace Craft;

class FeedMe_ProcessService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_processedElements = array();
    private $_service = null;
    private $_time_start = null;

    private $_criteria = null;
    private $_data = null;
    private $_additionalOptions = null;


    // Public Methods
    // =========================================================================

    public function setupForProcess($feed, $feedData)
    {
        if (!$feedData) {
            throw new Exception(Craft::t('No data to import.'));
        }

        $return = $feed->attributes;

        // Set our start time to track feed processing time
        $this->_time_start = microtime(true); 

        // Add some additional information to our FeedModel - for ease of use in processing
        $return['fields'] = array();
        $return['existingElements'] = array();

        if (!$feed['fieldMapping']) {
            throw new Exception(Craft::t('Field mapping not setup.'));
        }

        // Start looping through all the mapped fields - checking for nested nodes
        foreach ($feed['fieldMapping'] as $itemNode => $destination) {
            // Forget about any fields mapped as not to import
            if ($destination != 'noimport') {
                $return['fields'][$itemNode] = $destination;
            }
        }

        // Get the service for the Element Type we're dealing with
        if (!($this->_service = craft()->feedMe->getElementTypeService($feed['elementType']))) {
            throw new Exception(Craft::t('Unknown Element Type Service called.'));
        }

        // If our duplication handling is to delete - we delete all elements
        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete) {
            $criteria = $this->_service->setCriteria($feed);

            $return['existingElements'] = $criteria->ids();
        }

        // Setup a bunch of variables that can be done once-off at the start of feed processing
        // rather than on each step. This is done for max performance - even a little
        $this->_criteria = $this->_service->setCriteria($feed);

        foreach ($feedData as $key => $nodeData) {
            $this->_data[$key] = $this->_prepFieldData($return['fields'], $nodeData);
            $this->_additionalOptions[$key] = $this->_getAdditionalFieldOptions($return['fields'], $nodeData);
        }

        return $return;
    }

    public function processFeed($step, $feed, $debug = false)
    {
        $existingElement = false;
        $fieldData = array();
        $uniqueMatches = array();

        //
        // Lets get started!
        //

        // Set up a model for this Element Type
        $element = $this->_service->setModel($feed);

        // Set criteria according to Element Type 
        $criteria = $this->_criteria;

        // From the raw data in our feed, process it ready for mapping (more to do below)
        $data = $this->_data[$step];

        // Grab a few more things form our feed - essentially just amalgamating additional options or settings
        // Includes additional options for fields (checkboxes), and element fields (if any)
        // We also store the original feed-node handle for the field mapping, which is handy for conditionals
        $additionalOptions = $this->_additionalOptions[$step];

        // For each chunck of import-ready data, we need to further prepare it for Craft
        foreach ($data as $handle => $preppedData) {
            $options = array();

            if (isset($additionalOptions[$handle])) {
                $options = $additionalOptions[$handle];
            }

            // Check for our default data (if any provided, and if not already set in 'real' data)
            if (($preppedData == '__') && isset($feed['fieldDefaults'][$handle])) {
                $preppedData = $feed['fieldDefaults'][$handle];
            }

            // From each field, we may need to process the raw data for Craft fields
            $content = craft()->feedMe_fields->prepForFieldType($element, $preppedData, $handle, $options);

            // Setup a new array with the data mapped to the correct Craft field
            $fieldData[$handle] = $content;
        }

        //
        // Check for Add/Update/Delete for existing elements
        //

        // Check to see if an element already exists
        $existingElements = $this->_service->matchExistingElement($criteria, $fieldData, $feed);

        if (isset($existingElements[0])) {
            $existingElement = $existingElements[0];
        }

        // If there's an existing matching element
        if ($existingElement && $feed['duplicateHandle']) {

            // If we're deleting or updating an existing element, we want to focus on that one
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete || $feed['duplicateHandle'] == FeedMe_Duplicate::Update) {
                $element = $existingElement;
            }

            // If we're adding, and there's an existing element - quit now
            if ($feed['duplicateHandle'] == FeedMe_Duplicate::Add) {
                return;
            }
        }

        // Prepare Element Type model - this sets all Element Type attributes (Title, slug, etc).
        // Run once to setup defaults, second time for actual data from feed
        $element = $this->_service->prepForElementModel($element, $feed['fieldDefaults'], $feed, $additionalOptions);
        $element = $this->_service->prepForElementModel($element, $fieldData, $feed, $additionalOptions);

        // Allow field types to modify content once an element has been properly setup and identified
        foreach ($data as $handle => $preppedData) {
            craft()->feedMe_fields->postForFieldType($element, $fieldData, $handle, $handle);
        }

        // Set the Element Type's fields data
        $element->setContentFromPost($fieldData);
        
        // Save the element
        if ($this->_service->save($element, $feed)) {
            // Give elements a chance to perform actions after save
            $this->_service->afterSave($element, $fieldData, $feed);

            if ($existingElement) {
                FeedMePlugin::log($feed['name'] . ': ' . $feed['elementType'] . ' ' . $element->id . ' updated.', LogLevel::Info, true);
            } else {
                FeedMePlugin::log($feed['name'] . ': ' . $feed['elementType'] . ' ' . $element->id . ' added.', LogLevel::Info, true);
            }

            // Store our successfully processed element for feedback in logs, but also in case we're deleting
            $this->_processedElements[] = $element->id;
        } else {
            if ($element->getErrors()) {
                throw new Exception(json_encode($element->getErrors()));
            } else {
                throw new Exception(Craft::t('Unknown Element error occurred.'));
            }
        }

        $this->_debugOutput($debug, $fieldData);
    }

    public function finalizeAfterProcess($settings, $feed)
    {
        if ($feed['duplicateHandle'] == FeedMe_Duplicate::Delete) {
            $deleteIds = array_diff($settings['existingElements'], $this->_processedElements);
            $criteria = $this->_service->setCriteria($feed);
            $criteria->id = $deleteIds;
            $elementsToDelete = $criteria->find();

            if ($elementsToDelete) {
                if ($this->_service->delete($elementsToDelete)) {
                    FeedMePlugin::log($feed->name . ': The following elements have been deleted: ' . print_r($deleteIds, true) . '.', LogLevel::Info, true);
                } else {
                    if ($element->getErrors()) {
                        throw new Exception(json_encode($element->getErrors()));
                    } else {
                        throw new Exception(Craft::t('Something went wrong while deleting elements.'));
                    }
                }
            }
        }

        // Log the total time taken to process the feed
        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $this->_time_start), 2);
        FeedMePlugin::log($feed->name . ': Processing ' . count($this->_processedElements) . ' elements finished in ' . $execution_time . 's', LogLevel::Info, true);

        $this->_debugOutput(true, 'Processing ' . count($this->_processedElements) . ' elements finished in ' . $execution_time . 's.');
    }

    public function debugFeed($feedId, $limit)
    {
        $feed = craft()->feedMe_feeds->getFeedById($feedId);

        $feedData = craft()->feedMe_data->getFeed($feed->feedType, $feed->feedUrl, $feed->primaryElement, $feed);
        $feedSettings = craft()->feedMe_process->setupForProcess($feed, $feedData);

        // Do we even have any data to process?
        if (!count($feedData)) {
            $this->_debugOutput(true, 'No feed items to process.');
            return true;
        }

        foreach ($feedData as $key => $data) {
            craft()->feedMe_process->processFeed($key, $feedSettings, true);

            if ($key === ($limit - 1)) {
                break;
            }
        }

        craft()->feedMe_process->finalizeAfterProcess($feedSettings, $feed);
    }
    


    // Event Handlers
    // =========================================================================

    public function onBeforeProcessFeed(\CEvent $event)
    {
        $this->raiseEvent('onBeforeProcessFeed', $event);
    }

    public function onStepProcessFeed(\CEvent $event)
    {
        $this->raiseEvent('onStepProcessFeed', $event);
    }

    public function onProcessFeed(\CEvent $event)
    {
        $this->raiseEvent('onProcessFeed', $event);
    }



    // Private Methods
    // =========================================================================

    private function _prepFieldData($fields, $feedData)
    {
        $temp = array();

        foreach ($fields as $handle => $feedHandle) {
            $data = $this->_getValueFromKeyPath($feedData, $feedHandle);

            if ($data == '__') {
                //continue;
            }

            // Handle sub-fields and assigning to correct indexes
            if (strstr($handle, '--')) {
                $subFields = explode('--', $handle);

                $this->_arraySetFromPath($temp, $subFields, $data);
            } else {
                $temp[$handle] = $data;
            }
        }

        return $temp;
    }

    // Allow to fetch a value from an array with a given key path - 'key/to/value'
    // but also support wildcards for nested arrays - 'key/to/.../values'
    private function _getValueFromKeyPath($array, $path)
    {
        if (is_array($path)) {
            $keys = $path;
        } else {
            if (is_array($array)) {
                if (array_key_exists($path, $array)) {
                    return $array[$path];
                }
            }

            $delimiter = '/';
            $path = ltrim($path, "{$delimiter} ");
            $path = rtrim($path, "{$delimiter} ...");
            $keys = explode($delimiter, $path);
        }

        do {
            $key = array_shift($keys);

            if (ctype_digit($key)) {
                $key = (int)$key;
            }

            if (isset($array[$key])) {
                if ($keys) {
                    if (is_array($array[$key])) {
                        $array = $array[$key];
                    } else {
                        break;
                    }
                } else {
                    return $array[$key];
                }
            } elseif ($key === '...') {
                $values = array();

                foreach ($array as $i => $arr) {
                    $nestedData = $this->_getValueFromKeyPath($arr, implode('/', $keys));

                    if ($nestedData != '__') {
                        $values[$i] = $nestedData;
                    }
                }

                if ($values) {
                    return $values;
                } else {
                    break;
                }
            } else {
                break;
            }
        } while ($keys);

        return '__';
    }

    // Allows us to build an array with a provided path =  'key/to/value' turns into array[key][to][value] = $value
    private function _arraySetFromPath(&$arr, $path, $value)
    {
        if (!$path) {
            return null;
        }

        $segments = is_array($path) ? $path : explode('/', $path);
        $cur = &$arr;

        foreach ($segments as $segment) {
            if (!isset($cur[$segment])) {
                $cur[$segment] = array();
            }

            $cur = &$cur[$segment];
        }

        $cur = $value;
    }

    private function _multiExplode($delimiters, $string) {
        $ready = str_replace($delimiters, '/', $string);
        $launch = explode('/', $ready);
        return $launch;
    }

    private function _getAdditionalFieldOptions($fields, $feedData)
    {
        $array = array();

        foreach ($fields as $handle => $feedHandle) {
            if (strstr($handle, '--')) {
                $topFieldHandle = explode('--', $handle);
                $topFieldHandle = $topFieldHandle[0];
            } else {
                $topFieldHandle = $handle;
            }

            if ($feedHandle && !strstr($handle, '-options-') && !strstr($handle, '-fields-')) {
                $array['feedHandle'][$topFieldHandle][] = $feedHandle;
            }

            // Handle additional options - checkboxes for things to perform for field
            if (strstr($handle, '-options-')) {
                $subFields = explode('-options-', $handle);

                if ($feedHandle) {
                    if (strstr($handle, '--')) {
                        $opts = explode('--', $subFields[0]);
                        $opts[] = $subFields[1];

                        $this->_arraySetFromPath($array['options'], $opts, $feedHandle);
                    } else {
                        $this->_arraySetFromPath($array['options'], $subFields, $feedHandle);
                    }
                }
            }

            // Handle element sub-fields (ie - element fields for an asset field)
            if (strstr($handle, '-fields-')) {
                $data = $this->_getValueFromKeyPath($feedData, $feedHandle);

                $fieldHandles = $this->_multiExplode(array('--', '-fields-', '-'), $handle);

                $this->_arraySetFromPath($array['fields'], $fieldHandles, $data);
            }
        }

        $return = array();
        foreach ($array as $opt => $value) {
            foreach ($value as $handle => $arr) {
                foreach ($arr as $k => $a) {
                    $return[$handle][$opt][$k] = $a;
                }
            }
        }

        return $return;
    }

    private function _debugOutput($debug, $data)
    {
        if ($debug) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
    }
}
