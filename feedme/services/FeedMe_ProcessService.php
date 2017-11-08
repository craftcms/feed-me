<?php
namespace Craft;

use Cake\Utility\Hash as Hash;

class FeedMe_ProcessService extends BaseApplicationComponent
{
    // Properties
    // =========================================================================

    private $_debug = false;
    private $_processedElements = array();
    private $_processedElementIds = array();
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

        // A simple license check
        if ($feed['elementType'] != 'Entry') {
            if (!craft()->feedMe_license->isProEdition()) {
                throw new Exception(Craft::t('Feed Me is not licensed.'));
            }
        }

        $return = $feed->attributes;

        // Set our start time to track feed processing time
        $this->_time_start = microtime(true); 

        craft()->config->maxPowerCaptain();

        // Reset properties to allow an instance of this service to be reused
        $this->_processedElements = array();
        $this->_processedElementIds = array();

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
        // If our duplication handling is to disable - we disable all elements
        if (FeedMeDuplicate::isDelete($feed) || FeedMeDuplicate::isDisable($feed)) {
            $criteria = $this->_service->setCriteria($feed);

            $return['existingElements'] = $criteria->ids();
        }

        // Setup a bunch of variables that can be done once-off at the start of feed processing
        // rather than on each step. This is done for max performance - even a little
        $this->_criteria = $this->_service->setCriteria($feed);

        $mappingPaths = craft()->feedMe_data->getFeedMapping($feedData);
        $contentNodes = craft()->feedMe_data->getContentMapping($feedData);

        // Our main data-parsing function. Handles the actual data values, defaults and field options
        foreach ($contentNodes as $key => $nodeData) {
            $this->_data[$key] = $this->_prepFieldData($return['fields'], $nodeData, $feedData, $feed['fieldDefaults']);
        }

        return $return;
    }

    public function processFeed($step, $feed)
    {
        $existingElement = false;
        $fieldData = array();
        $uniqueMatches = array();

        // We can opt-out of updating certain elements if a field is switched on
        $skipUpdateFieldHandle = craft()->config->get('skipUpdateFieldHandle', 'feedMe');

        //
        // Lets get started!
        //

        // Set up a model for this Element Type
        $element = $this->_service->setModel($feed);

        // Set criteria according to Element Type 
        $criteria = $this->_service->setCriteria($feed);

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
        $existingElement = $this->_service->matchExistingElement($criteria, $fieldData, $feed);

        // If there's an existing matching element
        if ($existingElement) {

            // If we're deleting or updating an existing element, we want to focus on that one
            if (FeedMeDuplicate::isUpdate($feed)) {
                $element = $existingElement;
            }

            // There's also a config settings for a field to opt-out of updating. Check against that
            if ($skipUpdateFieldHandle) {
                $updateField = $element->content->getAttribute($skipUpdateFieldHandle);

                // We've got our special field on this element, and its switched on
                if ($updateField === '1') {
                    return;
                }
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

            // If this variable is explicitly false, this means there's no data in the feed for mapping
            // existing elements - thats a problem no matter which option is selected, so don't proceed.
            // Even if Add is selected, we'll end up with duplicates because it can't find existing elements to skip over
            if ($existingElement === false) {
                return;
            }
        }

        // Prepare Element Type model - this sets all Element Type attributes (Title, slug, etc).
        $element = $this->_service->prepForElementModel($element, $fieldData, $feed);

        // Allow field types to modify content once an element has been properly setup and identified
        foreach ($data as $handle => $preppedData) {
            craft()->feedMe_fields->postForFieldType($element, $fieldData, $handle);

            if (craft()->config->get('checkExistingFieldData', 'feedMe')) {
                craft()->feedMe_fields->checkExistingFieldData($element, $fieldData, $handle);
            }
        }

        // Set the Element Type's fields data - but only if we're not targeting a locale
        if (!$feed['locale']) {
            $element->setContentFromPost($fieldData);
        }

        //$this->_debugOutput($element->attributes);
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
            $this->_processedElementIds[] = $element->id;

            // Also store our element in-memory to prevent garbage collection of the variable. This is due to an odd error,
            // specific to Assets where incorrect content will be fetched from a content cache.
            //
            // This is to do with spl_object_hash, which an asset field uses for cached content.
            // It relies on the owner element (this element) being hashed as a key to a private variable
            // The hash doesn't look at content for the ElementModel, and instead looks at its in-memory pointer.
            // As such, if the ElementModel is no longer referenced anywhere, it'll be garbage collected, and the hash
            // function will generate the same hash for a new object. 
            //
            // This causes the asset field to think it already has data, but its incorrect.
            //
            //$this->_processedElements[] = $element;

            return $element;
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
        if (FeedMeDuplicate::isDisable($feed)) {
            $disableIds = array_diff($settings['existingElements'], $this->_processedElementIds);
            $criteria = $this->_service->setCriteria($feed);
            $criteria->id = $disableIds;
            $criteria->status = true;
            $elementsToDisable = $criteria->find();

            if ($elementsToDisable) {
                if ($this->_service->disable($elementsToDisable)) {
                    FeedMePlugin::log($feed->name . ': The following elements have been disabled: ' . print_r($disableIds, true) . '.', LogLevel::Info, true);
                }
            }
        }

        if (FeedMeDuplicate::isDelete($feed)) {
            if (FeedMeDuplicate::isDisable($feed)) {
                FeedMePlugin::log($feed->name . ":  You can't have Delete and Disabled enabled at the same time as an Import Strategy.", LogLevel::Info, true);
                return;
            }

            $deleteIds = array_diff($settings['existingElements'], $this->_processedElementIds);
            $criteria = $this->_service->setCriteria($feed);
            $criteria->id = $deleteIds;
            $elementsToDelete = $criteria->find();

            if ($elementsToDelete) {
                if ($this->_service->delete($elementsToDelete)) {
                    FeedMePlugin::log($feed->name . ': The following elements have been deleted: ' . print_r($deleteIds, true) . '.', LogLevel::Info, true);
                }
            }
        }

        // Log the total time taken to process the feed
        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $this->_time_start), 2);
        FeedMePlugin::log($feed->name . ': Processing ' . count($this->_processedElementIds) . ' elements finished in ' . $execution_time . 's', LogLevel::Info, true);

        $this->_debugOutput('Processing ' . count($this->_processedElementIds) . ' elements finished in ' . $execution_time . 's.');
    }

    public function debugFeed($feedId, $limit, $offset)
    {
        $this->_debug = true;

        $feed = craft()->feedMe_feeds->getFeedById($feedId);

        $feedData = craft()->feedMe_data->getFeed($feed->feedType, $feed->feedUrl, $feed->primaryElement, $feed);

        if ($offset) {
            $feedData = array_slice($feedData, $offset);
        }

        $feedSettings = craft()->feedMe_process->setupForProcess($feed, $feedData);

        // Fire an "onBeforeProcessFeed" event
        $event = new Event($this, array('settings' => $feedSettings));
        craft()->feedMe_process->onBeforeProcessFeed($event);

        // Do we even have any data to process?
        if (!count($feedData)) {
            $this->_debugOutput('No feed items to process.');
            return true;
        }

        foreach ($feedData as $key => $data) {
            $element = craft()->feedMe_process->processFeed($key, $feedSettings);

            // Fire an "onStepProcessFeed" event
            $event = new Event($this, array('settings' => $feedSettings, 'element' => $element));
            craft()->feedMe_process->onStepProcessFeed($event);

            if ($key === ($limit - 1)) {
                break;
            }
        }

        craft()->feedMe_process->finalizeAfterProcess($feedSettings, $feed);

        // Fire an "onProcessFeed" event
        $event = new Event($this, array('settings' => $feedSettings));
        craft()->feedMe_process->onProcessFeed($event);
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

    private function _prepFieldData($fieldMapping, $contentNode, $feedData, $fieldDefaults)
    {
        $parsedData = array();

        // First, loop through all the field defaults. Important to do first, as if we set a default
        // but aren't actually mapping it to anything, we'll never enter the below loop
        if (is_array($fieldDefaults)) {
            foreach ($fieldDefaults as $fieldHandle => $feedHandle) {
                if (isset($feedHandle) && $feedHandle !== '') {
                    if (strstr($fieldHandle, '--')) {
                        $split = FeedMeArrayHelper::multiExplode(array('--', '-'), $fieldHandle);

                        array_splice($split, 1, 0, 'data');

                        $keyPath = implode('.', $split);

                        $parsedData[$keyPath]['data'] = $feedHandle;

                        $parsedData = Hash::expand($parsedData);
                    } else {
                        $parsedData[$fieldHandle]['data'] = $feedHandle;
                    }
                }
            }
        }

        // Now, onto grabbing our content from the feed. This is done by first looking at all the nodes
        // in the feed, and looping through them. This importantly keeps their order in the feed (important
        // for Matrix), and ensures we don't miss data. This has the added benefit of abstracting complex
        // querying based on what the user maps, and the overall structure of the feed. Typically, XML
        // is difficult to determine repeatable content consistently, without its array-specifying syntax.
        //
        // We might have something like Assets/Asset/Img stored in our field mapping, but this is useless
        // and ambiguous to look up nodes. Instead, look each node directly like 0.Assets.Asset.0.Img.0

        foreach ($contentNode as $j => $nodePath) {
            $feedPath = str_replace('.', '/', $nodePath);
            $feedPath = preg_replace('/(\/\d+\/)/', '/', $feedPath);
            $feedPath = preg_replace('/(\/\d+)|(\/\d+\/)/', '', $feedPath);
            $feedPath = preg_replace('/(\/\d+)|^(\d+\/)/', '', $feedPath);

            // Get the feed value using dot-notation (but specifically for a node)
            $value = Hash::get($feedData, $nodePath);

            // Get the correct Craft field handle the user has chosen for this feed element
            $fieldHandles = FeedMeArrayHelper::findKeyByValue($fieldMapping, $feedPath);

            if ($fieldHandles) {
                foreach ($fieldHandles as $fieldHandle => $feedHandle) {
                    if (strstr($fieldHandle, '--')) {
                        $split = FeedMeArrayHelper::multiExplode(array('--', '-'), $fieldHandle);

                        // Handle multiple nested content (Matrix, Table, etx)
                        preg_match_all('/\.(\d+)\./', $nodePath, $matches);

                        if (isset($matches[1][0]) && $matches[1][0] != '') {
                            array_splice($split, 1, 0, $matches[1][0]);
                        }

                        if (isset($matches[1][1]) && $matches[1][1] != '') {
                            array_splice($split, 4, 0, $matches[1][1]);
                        }

                        array_splice($split, 1, 0, 'data');

                        $keyPath = implode('.', $split);
                    } else {
                        $keyPath = $fieldHandle;
                    }

                    // Parse nested fields' data
                    if (strstr($fieldHandle, '-fields-')) {
                        $keyPath = str_replace('-', '.', $keyPath);
                    }


                    // Create a dot-notation path for our values, rather than iterating, merging, etc.
                    // But be careful not to do this for field options which may very well match a field/element handle
                    // for example - category-options-match = 'title' would grab the data for 'title' in the feed.
                    if (!strstr($keyPath, '-options')) {
                        // Check if we need to merge with existing data already processed in the feed
                        if (isset($parsedData[$keyPath . '.data'])) {
                            $valueArray = $parsedData[$keyPath . '.data'];

                            if (!is_array($valueArray)) {
                                $valueArray = array($valueArray);
                            }

                            $valueArray[] = $value;
                            $parsedData[$keyPath . '.data'] = $valueArray;
                        } else {
                            $parsedData[$keyPath . '.data'] = $value;
                        }
                    }


                    // Check if this field has any related data? Commonly Element fields have this.
                    // Attach any options to the same node as the Craft Field, so its nicely organised.
                    $options = FeedMeArrayHelper::findByPartialKey($fieldMapping, $fieldHandle . '-options');

                    if ($options) {
                        foreach ($options as $optionKey => $optionValue) {
                            $optionKeyOption = preg_replace('/.+?(?<=-options-)/', '', $optionKey);
                            $optionKeyPath = $keyPath . '.options.' . $optionKeyOption;

                            // Make sure we look at the correct path - it'll be the same as where `data` sits
                            $parsedData[$optionKeyPath] = $optionValue;
                        }
                    }
                }
            }
        }

        // A little extra work here for nested field content, specifically for dealing with
        // Matrix and other complex data types. They can fetch fields not necessarily on the same node
        // which means they loose the same block order for their main data. For instance:
        // matrixAssets.data.block1.assets1.fields.plainText.data whereas data will be on an indexed node like
        // matrixAssets.data.0.block1.assets1.fields.plainText.data, so we need to check for all that
        /*foreach ($parsedData as $fieldHandle => $value) {
            $nodePath = preg_replace('/(\.data)$/', '', $fieldHandle);
            $tempHandle = preg_replace('/(\.\d+\.)/', '.', $nodePath);

            $fields = FeedMeArrayHelper::findByPartialKey($parsedData, $tempHandle . '.fields');

            // If we've found some related, nested field data, make sure we shift it to the correct key
            if ($fields) {
                foreach ($fields as $fieldKey => $fieldValue) {
                    $fieldKeyField = preg_replace('/.+?(?<=\.fields\.)/', '', $fieldKey);
                    $newKeyPath = $nodePath . '.fields.' . $fieldKeyField;

                    // Add the value under the correct key path
                    $parsedData[$newKeyPath] = $fieldValue;

                    // Remove the old data from the array, as its been shifted
                    unset($parsedData[$fieldKey]);
                }
            }     
        }*/

        // Handy magic function to expand dot-notation keys to multi-dimensions array
        $parsedData = Hash::expand($parsedData);

        //$this->_debugOutput($parsedData);

        return $parsedData;
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
