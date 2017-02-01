<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMe_ProcessService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_debug = false;
    private $_processedElements = array();
    private $_service = null;
    private $_time_start = null;

    private $_criteria = null;
    private $_data = null;


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
        if (FeedMeDuplicate::isDelete($feed)) {
            $criteria = $this->_service->setCriteria($feed);

            $return['existingElements'] = $criteria->ids();
        }

        // Setup a bunch of variables that can be done once-off at the start of feed processing
        // rather than on each step. This is done for max performance - even a little
        $this->_criteria = $this->_service->setCriteria($feed);

        // Our main data-parsing function. Handles the actual data values, defaults and field options
        foreach ($feedData as $key => $nodeData) {
            $this->_data[$key] = $this->_prepFieldData($return['fields'], $nodeData, $feed['fieldDefaults']);
        }

        return $return;
    }

    public function processFeed($step, $feed)
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

        // For each chunck of import-ready data, we need to further prepare it for Craft
        foreach ($data as $handle => $preppedData) {
            // From each field, we may need to process the raw data for Craft fields
            $fieldData[$handle] = craft()->feedMe_fields->prepForFieldType($element, $preppedData, $handle);
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
        if ($existingElement) {

            // If we're deleting or updating an existing element, we want to focus on that one
            if (FeedMeDuplicate::isUpdate($feed)) {
                $element = $existingElement;
            }

            // If we're adding only, and there's an existing element - quit now
            if (FeedMeDuplicate::isAdd($feed, true)) {
                return;
            }
        } else {
            // Have we set to update-only? There are no existing elements, so skip
            if (FeedMeDuplicate::isUpdate($feed, true)) {
                return;
            }
        }

        // Prepare Element Type model - this sets all Element Type attributes (Title, slug, etc).
        $element = $this->_service->prepForElementModel($element, $fieldData, $feed);

        // Allow field types to modify content once an element has been properly setup and identified
        foreach ($data as $handle => $preppedData) {
            craft()->feedMe_fields->postForFieldType($element, $fieldData, $handle, $handle);
        }

        // Set the Element Type's fields data - but only if we're not targeting a locale
        if (!$feed['locale']) {
            $element->setContentFromPost($fieldData);
        }

        $this->_debugOutput($element->attributes);
        $this->_debugOutput($fieldData);
        
        // Save the element
        if ($this->_service->save($element, $fieldData, $feed)) {
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
                throw new Exception(Craft::t('Unknown Element saving error occurred.'));
            }
        }
    }

    public function finalizeAfterProcess($settings, $feed)
    {
        if (FeedMeDuplicate::isDelete($feed)) {
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

        $this->_debugOutput('Processing ' . count($this->_processedElements) . ' elements finished in ' . $execution_time . 's.');
    }

    public function debugFeed($feedId, $limit)
    {
        $this->_debug = true;

        $feed = craft()->feedMe_feeds->getFeedById($feedId);

        $feedData = craft()->feedMe_data->getFeed($feed->feedType, $feed->feedUrl, $feed->primaryElement, $feed);
        $feedSettings = craft()->feedMe_process->setupForProcess($feed, $feedData);

        // Do we even have any data to process?
        if (!count($feedData)) {
            $this->_debugOutput('No feed items to process.');
            return true;
        }

        foreach ($feedData as $key => $data) {
            craft()->feedMe_process->processFeed($key, $feedSettings);

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

    private function _prepFieldData($fieldMapping, $feedData, $fieldDefaults)
    {
        $parsedData = array();

        // First, loop through all the field defaults. Important to do first, as if we set a default
        // but aren't actually mapping it to anything, we'll never enter the below loop
        if (is_array($fieldDefaults)){
            foreach ($fieldDefaults as $fieldHandle => $feedHandle) {
                if (isset($feedHandle) && $feedHandle !== '') {
                    $parsedData[$fieldHandle]['data'] = $feedHandle;
                }
            }
        }

        foreach ($fieldMapping as $fieldHandle => $feedHandle) {
            if ($feedHandle == 'noimport') {
                continue;
            }

            // We display and store field mapping with '/' and '/.../', for the users benefit,
            // but Extract needs them as '.' or '{*}', so we convert them here.
            // Turns 'my/repeating/.../field' into 'my.repeating.*.field'
            $extractFeedHandle = str_replace('/.../', '.*.', $feedHandle);
            $extractFeedHandle = str_replace('[]', '', $extractFeedHandle);
            $extractFeedHandle = str_replace('/', '.', $extractFeedHandle);

            // Have a default ready to go in case a value can't be found in the feed
            $defaultValue = isset($fieldDefaults[$fieldHandle]) ? $fieldDefaults[$fieldHandle] : null;

            // Get our value from the feed
            $value = FeedMeArrayHelper::arrayGet($feedData, $extractFeedHandle, $defaultValue);

            // Store it in our data array, with the Craft field handle we're mapping to
            if (isset($value) && $value !== '') {
                if (is_array($value)) {
                    // Our arrayGet() function keeps empty indexes, which is super-important
                    // for Matrix. Here, this filters them out, while keeping the indexes intact
                    $value = Hash::filter($value);
                }

                $parsedData[$fieldHandle]['data'] = $value;
            }

            // An annoying check for inconsistent nodes - I'm looking at you XML
            if (strstr($extractFeedHandle, '.*.')) {
                // Check for any single data. While we expect something like: [Assets/Asset/.../Img] => image_1.jpg
                // We often get data that can be mapped as: [Assets/Asset/Img] => image_3.jpg
                // So we check for both...
                $testSingleFeedHandle = str_replace('.*.', '.', $extractFeedHandle);
                $value = FeedMeArrayHelper::arrayGet($feedData, $testSingleFeedHandle, $defaultValue);

                if (isset($value) && $value !== '') {
                    $parsedData[$fieldHandle]['data'] = $value;
                }
            }

            // If this field has any options, add those to the above node, rather than separate
            if (strstr($fieldHandle, '-options-')) {

                $fieldHandles = FeedMeArrayHelper::multiExplode(array('--', '-'), $fieldHandle);

                if (strstr($fieldHandle, '--')) {
                    array_splice($fieldHandles, 1, 0, 'data');
                }

                FeedMeArrayHelper::arraySet($parsedData, $fieldHandles, $feedHandle);
                unset($parsedData[$fieldHandle]); // Remove un-needed original

            } else if (strstr($fieldHandle, '-fields-')) {

                // If this field has any nested fields, add those to the above node, rather than separate
                // Note this has to be recursive as there is field-mapping for these inner fields.
                $fieldHandles = FeedMeArrayHelper::multiExplode(array('--', '-'), $fieldHandle);

                if (strstr($fieldHandle, '--')) {
                    array_splice($fieldHandles, 1, 0, 'data');
                }

                $nestedData = $this->_getInnerFieldData($feedData, $feedHandle);

                FeedMeArrayHelper::arraySet($parsedData, $fieldHandles, $nestedData);
                unset($parsedData[$fieldHandle]); // Remove un-needed original

            } else if (strstr($fieldHandle, '--')) {
                // Some fields like a Table contain multiple blocks of data, each needing to be mapped individually
                // which means feed-mapping will give us something like below. We need to re-jig things.
                // [table--col1] => Array (
                //     [0] => Option1
                //     [1] => Option3
                // )
                // [table--col2] => Array (
                //     [0] => Option2
                //     [1] => Option4
                // )

                $split = explode('--', $fieldHandle);
                array_splice($split, 1, 0, 'data');

                $nestedData = $this->_getInnerFieldData($feedData, $feedHandle);

                FeedMeArrayHelper::arraySet($parsedData, $split, $nestedData);
                unset($parsedData[$fieldHandle]); // Remove un-needed original
            }
        }

        //$this->_debugOutput($parsedData);

        return $parsedData;
    }

    // A more lightweight version of our main feed-data-getting function
    // I suppose this could be recursive, but lets not make life harder than it already is...
    private function _getInnerFieldData($feedData, $feedHandle)
    {
        $parsedData = array();

        // We display and store field mapping with '/' and '/.../', for the users benefit,
        // but Extract needs them as '.' or '{*}', so we convert them here.
        // Turns 'my/repeating/.../field' into 'my.repeating.*.field'
        $extractFeedHandle = str_replace('/.../', '.*.', $feedHandle);
        $extractFeedHandle = str_replace('[]', '', $extractFeedHandle);
        $extractFeedHandle = str_replace('/', '.', $extractFeedHandle);

        // Use Extract to pull out our nested data. Super-cool!
        $value = FeedMeArrayHelper::arrayGet($feedData, $extractFeedHandle);

        // An annoying check for inconsistent nodes - I'm looking at you XML
        if (strstr($extractFeedHandle, '.*.')) {
            // Check for any single data. While we expect something like: [Assets/Asset/.../Img] => image_1.jpg
            // We often get data that can be mapped as: [Assets/Asset/Img] => image_3.jpg
            // So we check for both...
            //$testSingleFeedHandle = $this->str_lreplace('.*.', '.', $extractFeedHandle);
            $testSingleFeedHandle = $this->str_lreplace('.*.', '.', $extractFeedHandle);
            $tempValue = FeedMeArrayHelper::arrayGet($feedData, $testSingleFeedHandle);

            if (isset($tempValue) && $tempValue !== '') {
                $value = $tempValue;
            }
        }

        // Store it in our data array, with the Craft field handle we're mapping to
        if (isset($value) && $value !== '') {
            if (is_array($value)) {
                $value = Hash::filter($value);
            }

            if (strstr($feedHandle, '[]')) {
                $parsedData['data'] = array($value);
            } else {
                $parsedData['data'] = $value;
            }
        }

        return $parsedData;
    }

    private function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    private function _debugOutput($data)
    {
        if ($this->_debug) {
            echo '<pre>';
            print_r($data);
            echo '</pre>';
        }
    }
}
