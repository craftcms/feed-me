<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\helpers\DuplicateHelper;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;
use craft\models\Section;
use craft\events\RegisterElementSourcesEvent;

use Cake\Utility\Hash;

class Process extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_PROCESS_FEED = 'onBeforeProcessFeed';
    const EVENT_STEP_BEFORE_ELEMENT_MATCH = 'onStepBeforeElementMatch';
    const EVENT_STEP_BEFORE_ELEMENT_SAVE = 'onStepBeforeElementSave';
    const EVENT_STEP_AFTER_ELEMENT_SAVE = 'onStepElementSave';
    const EVENT_AFTER_PROCESS_FEED = 'onAfterProcessFeed';


    // Properties
    // =========================================================================

    private $_processedElements = [];
    private $_processedElementIds = [];
    private $_time_start = null;

    private $_service = null;
    private $_feed = null;
    private $_criteria = null;
    private $_data = null;


    // Public Methods
    // =========================================================================

    public function beforeProcessFeed($feed, $feedData)
    {
        FeedMe::info($feed, 'Preparing for feed processing.');

        if (!$feedData) {
            throw new \Exception(Craft::t('feed-me', 'No data to import.'));
        }

        // A simple license check
        if ($feed['elementType'] != 'craft\elements\Entry') {
            if (!FeedMe::$plugin->service->isProEdition()) {
                throw new \Exception(Craft::t('feed-me', 'Feed Me is not licensed.'));
            }
        }

        // Check for backup, best to do this before we do anything
        if ($feed->backup) {
            $this->_backupBeforeFeed($feed);
        }

        $this->_data = $feedData;
        $this->_feed = $feed;
        $this->_service = $feed->element;

        $return = $feed->attributes;

        // Set our start time to track feed processing time
        $this->_time_start = microtime(true);

        App::maxPowerCaptain();

        // Reset properties to allow an instance of this service to be reused
        $this->_processedElements = [];
        $this->_processedElementIds = [];

        // Add some additional information to our FeedModel - for ease of use in processing
        // $return['fields'] = [];
        $return['existingElements'] = [];

        // Clear out Feed Mapping and Field Uniques - we need to do some filtering
        $return['fieldMapping'] = $this->_filterUnmappedFields($feed['fieldMapping']);
        $return['fieldUnique'] = [];

        if (!$return['fieldMapping']) {
            throw new \Exception(Craft::t('feed-me', 'Field mapping not setup.'));
        }

        // Ditch all fields we aren't checking for uniques on. Just simplifies each run (we don't have to check)
        foreach ($feed['fieldUnique'] as $key => $value) {
            if ((int)$value === 1) {
                $return['fieldUnique'][$key] = $value;
            }
        }

        if (!$return['fieldUnique']) {
            throw new \Exception(Craft::t('feed-me', 'No unique fields checked.'));
        }

        // Get the service for the Element Type we're dealing with
        if (!$feed->element) {
            throw new \Exception(Craft::t('feed-me', 'Unknown Element Type Service called.'));
        }

        // If our duplication handling is to delete - we delete all elements
        // If our duplication handling is to disable - we disable all elements
        if (DuplicateHelper::isDelete($feed) || DuplicateHelper::isDisable($feed)) {
            $query = $feed->element->getQuery($feed);

            $return['existingElements'] = $query->ids();
        }

        // Our main data-parsing function. Handles the actual data values, defaults and field options
        foreach ($feedData as $key => $nodeData) {
            $this->_data[$key] = Hash::flatten($nodeData, '/');
        }

        // Fire an 'onBeforeProcessFeed' event
        $this->_triggerEvent(self::EVENT_BEFORE_PROCESS_FEED, [
            'feed' => $feed,
            'feedData' => $feedData,
        ]);

        FeedMe::info($feed, 'Finished preparing for feed processing.');
        FeedMe::info($feed, 'Starting feed processing.');

        return $return;
    }

    public function processFeed($step, $feed)
    {
        $existingElement = false;
        $uniqueMatches = [];

        $contentData = [];
        $attributeData = [];
        $fieldData = [];

        // We can opt-out of updating certain elements if a field is switched on
        $skipUpdateFieldHandle = FeedMe::$plugin->service->getConfig('skipUpdateFieldHandle');

        //
        // Lets get started!
        //

        // Set up a model for this Element Type
        $element = $this->_service->setModel($feed);

        // From the raw data in our feed, we need to fix it up so its Craft-ready for the element and fields
        $feedData = $this->_data[$step];

        // Parse the just the element attributes first. We use these in our field contexts, and need a fully-prepped element
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (Hash::get($fieldInfo, 'attribute')) {
                $attributeData[$fieldHandle] = $this->_service->parseAttribute($feedData, $fieldHandle, $fieldInfo);
            }
        }

        // Set the attributes for the element
        $element->setAttributes($attributeData);

        // Then, do the same for custom fields. Again, this should be done after populating the element attributes
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (Hash::get($fieldInfo, 'field')) {
                $fieldValue = FeedMe::$plugin->fields->parseField($feed, $element, $feedData, $fieldHandle, $fieldInfo);;

                if ($fieldValue !== null) {
                    $fieldData[$fieldHandle] = $fieldValue;
                }
            }
        }

        // Do the same with our custom field data
        $element->setFieldValues($fieldData);

        // We need to keep these separate to apply to the element but required when matching against existing elements
        $contentData = $attributeData + $fieldData;


        //
        // Check for Add/Update/Delete for existing elements
        //

        // Fire an 'onStepBeforeElementMatch' event
        $this->_triggerEvent(self::EVENT_STEP_BEFORE_ELEMENT_MATCH, [
            'feed' => $feed,
            'feedData' => $feedData,
            'contentData' => $contentData,
        ]);

        // Check to see if an element already exists
        $existingElement = $this->_service->matchExistingElement($contentData, $feed);

        // If there's an existing matching element
        if ($existingElement) {

            // If we're deleting or updating an existing element, we want to focus on that one
            if (DuplicateHelper::isUpdate($feed)) {
                $element = $existingElement;

                // Update our service with the correct element
                $this->_service->element = $element;
            }

            // There's also a config settings for a field to opt-out of updating. Check against that
            if ($skipUpdateFieldHandle) {
                $updateField = $element->$skipUpdateFieldHandle ?? '';

                // We've got our special field on this element, and its switched on
                if ($updateField === '1') {
                    return;
                }
            }

            // If we're adding only, and there's an existing element - quit now
            if (DuplicateHelper::isAdd($feed, true)) {
                return;
            }
        } else {
            // Have we set to update-only? There are no existing elements, so skip
            if (DuplicateHelper::isUpdate($feed, true)) {
                return;
            }

            // If this variable is explicitly false, this means there's no data in the feed for mapping
            // existing elements - thats a problem no matter which option is selected, so don't proceed.
            // Even if Add is selected, we'll end up with duplicates because it can't find existing elements to skip over
            if ($existingElement === false) {
                return;
            }
        }

        // Are we only disabling/deleting only, we need to quit right here
        if (DuplicateHelper::isDisable($feed, true) || DuplicateHelper::isDelete($feed, true)) {
            // If there's an existing element, we want to keep it, otherwise remove it
            if ($existingElement) {
                $this->_processedElementIds[] = $existingElement->id;
            }

            return;
        }

        // If we've just fetched an existing element, we need to re-apply our changed field data. The reason this is done
        // twice is we need to have the element attributes and custom fields populated before we try to find an existing element
        // but then we need to update that element once matched. It's the chicken and the egg!
        if ($existingElement) {
            // Do the same with our custom field data
            $element->setFieldValues($fieldData);

            // We need to keep these separate to apply to the element but required when matching against existing elements
            $contentData = $attributeData + $fieldData;
        }

        FeedMe::debug($feed, $contentData);


        //
        // It's time to actually save the element!
        //

        // Fire an 'onStepBeforeElementSave' event
        $this->_triggerEvent(self::EVENT_STEP_BEFORE_ELEMENT_SAVE, [
            'feed' => $feed,
            'feedData' => $feedData,
            'contentData' => $contentData,
            'element' => $element,
        ]);

        // Save the element
        if ($this->_service->save($contentData, $feed)) {
            // Give elements a chance to perform actions after save
            $this->_service->afterSave($contentData, $feed);

            // Fire an 'onStepElementSave' event
            $this->_triggerEvent(self::EVENT_STEP_AFTER_ELEMENT_SAVE, [
                'feed' => $feed,
                'feedData' => $feedData,
                'contentData' => $contentData,
                'element' => $element,
            ]);

            if ($existingElement) {
                FeedMe::info($feed, $this->_service->displayName() . ' ' . $element->id . ' updated.');
            } else {
                FeedMe::info($feed, $this->_service->displayName() . ' ' . $element->id . ' added.');
            }

            // Store our successfully processed element for feedback in logs, but also in case we're deleting
            $this->_processedElementIds[] = $element->id;

            return $element;
        } else {
            if ($element->getErrors()) {
                throw new \Exception(json_encode($element->getErrors()));
            } else {
                throw new \Exception(Craft::t('feed-me', 'Unknown Element saving error occurred.'));
            }
        }
    }

    public function afterProcessFeed($settings, $feed)
    {
        if (DuplicateHelper::isDelete($feed) && DuplicateHelper::isDisable($feed)) {
            FeedMe::info($feed, "You can't have Delete and Disabled enabled at the same time as an Import Strategy.");
            return;
        }

        $elementsToDeleteDisable = array_diff($settings['existingElements'], $this->_processedElementIds);

        if ($elementsToDeleteDisable) {
            if (DuplicateHelper::isDisable($feed)) {
                $this->_service->disable($elementsToDeleteDisable);
                
                $message = 'The following elements have been disabled: ' . json_encode($elementsToDeleteDisable) . '.';
            } else {
                $this->_service->delete($elementsToDeleteDisable);
                
                $message = 'The following elements have been deleted: ' . json_encode($elementsToDeleteDisable) . '.';
            }

            FeedMe::info($feed, $message);
            FeedMe::debug($feed, $message);
        }

        // Log the total time taken to process the feed
        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $this->_time_start), 2);

        $message = 'Processing ' . count($this->_processedElementIds) . ' elements finished in ' . $execution_time . 's';
        FeedMe::info($feed, $message);
        FeedMe::debug($feed, $message);

        // Fire an 'onProcessFeed' event
        $this->_triggerEvent(self::EVENT_AFTER_PROCESS_FEED, [
            'feed' => $feed,
        ]);
    }

    public function debugFeed($feed, $limit, $offset)
    {
        $feed->debug = true;

        $feedData = $feed->getFeedData();

        if ($offset) {
            $feedData = array_slice($feedData, $offset);
        }

        if ($limit) {
            $feedData = array_slice($feedData, 0, $limit);
        }

        $totalSteps = count($feedData);

        // Do we even have any data to process?
        if (!$totalSteps) {
            FeedMe::debug($feed, 'No feed items to process.');
            return;
        }

        $feedSettings = $this->beforeProcessFeed($feed, $feedData);

        foreach ($feedData as $key => $data) {
            $element = $this->processFeed($key, $feedSettings);
        }

        $this->afterProcessFeed($feedSettings, $feed);
    }



    // Private Methods
    // =========================================================================

    private function _triggerEvent($event, $variables)
    {
        if ($this->hasEventHandlers($event)) {
            $this->trigger($event, new FeedProcessEvent($variables));
        }
    }

    private function _backupBeforeFeed($feed)
    {
        $limit = FeedMe::$plugin->service->getConfig('backupLimit') ?? 100;

        $backupPath = Craft::$app->getPath()->getDbBackupPath();

        if (is_dir($backupPath)) {
            // Check for any existing backups, if more than our limit, we need to kill some off...
            $currentBackups = FileHelper::findFiles($backupPath, [
                'only' => ['feedme-*.sql'],
                'recursive' => false
            ]);

            // Remove all the previous backups, except the amount we want to limit
            $backupsToDelete = [];

            if (is_array($currentBackups)) {
                if (count($currentBackups) > $limit) {
                    $backupsToDelete = array_splice($currentBackups, 0, (count($currentBackups) - $limit));
                }
            }

            // If we have any to remove, lets delete them
            if (count($backupsToDelete)) {
                foreach ($backupsToDelete as $file) {
                    FileHelper::unlink($file);
                }
            }
        }

        FeedMe::info($feed, 'Starting database backup');

        $file = $backupPath.'/feedme-'.gmdate('ymd_His').'_'.strtolower(StringHelper::randomString(10)).'.sql';

        Craft::$app->getDb()->backupTo($file);

        FeedMe::info($feed, 'Finished database backup');
    }

    // Function to be recursively called to weed out fields that are set to 'noimport'. More complex than usual by the fact
    // that complex fields (Table, Matrix) have multiple fields, some of which aren't mapped. This is why all nested fields
    // should be templated through the 'fields' index, and this function will take care of things from there.
    private function _filterUnmappedFields($fields)
    {
        $return = [];

        foreach ($fields as $key => $value) {
            $node = Hash::get($value, 'node');
            $nestedFields = Hash::get($value, 'fields');

            if ($nestedFields) {
                $value['fields'] = $this->_filterUnmappedFields($nestedFields);
            }

            if ($node !== 'noimport') {
                $return[$key] = $value;
            }
        }

        return $return;
    }
}
