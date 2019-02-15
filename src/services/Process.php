<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\helpers\DataHelper;
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
    const EVENT_STEP_BEFORE_PARSE_CONTENT = 'onStepBeforeParseContent';
    const EVENT_STEP_BEFORE_ELEMENT_SAVE = 'onStepBeforeElementSave';
    const EVENT_STEP_AFTER_ELEMENT_SAVE = 'onStepElementSave';
    const EVENT_AFTER_PROCESS_FEED = 'onAfterProcessFeed';


    // Properties
    // =========================================================================

    private $_time_start = null;

    private $_service = null;
    private $_feed = null;
    private $_criteria = null;
    private $_data = null;


    // Public Methods
    // =========================================================================

    public function beforeProcessFeed($feed, $feedData)
    {
        FeedMe::$feedName = $feed->name;

        FeedMe::info('Preparing for feed processing.');

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
            if (!is_array($nodeData)) {
                $nodeData = [$nodeData];
            }

            $this->_data[$key] = Hash::flatten($nodeData, '/');
        }

        // Fire an 'onBeforeProcessFeed' event
        $event = new FeedProcessEvent([
            'feed' => $this->_feed,
            'feedData' => $this->_data,
        ]);

        $this->trigger(self::EVENT_BEFORE_PROCESS_FEED, $event);

        if (!$event->isValid) {
            return;
        }

        // Allow event to modify variables
        $this->_feed = $event->feed;
        $this->_data = $event->feedData;

        FeedMe::info('Finished preparing for feed processing.');

        return $return;
    }

    public function processFeed($step, $feed, &$processedElementIds)
    {
        $existingElement = false;
        $uniqueMatches = [];

        $contentData = [];
        $attributeData = [];
        $fieldData = [];

        // We can opt-out of updating certain elements if a field is switched on
        $skipUpdateFieldHandle = FeedMe::$plugin->service->getConfig('skipUpdateFieldHandle', $feed['id']);

        //
        // Lets get started!
        //

        $logKey = StringHelper::randomString(20);

        // Save this to session so we don't have to pass it around everywhere.
        FeedMe::$stepKey = $logKey;

        // Try to fix an elusive bug...
        if (!is_numeric($step)) {
            FeedMe::error('Error `{i}`.', ['i' => json_encode($step)]);
        }

        FeedMe::info('Starting processing of node `#{i}`.', ['i' => ($step + 1)]);

        // Set up a model for this Element Type
        $element = $this->_service->setModel($feed);

        // From the raw data in our feed, we need to fix it up so its Craft-ready for the element and fields
        $feedData = $this->_data[$step];

        // We need to first find a potentially existing element, and to do that, we need to prep just the fields
        // that are selected as the unique identifier. We prep everything else later on.
        $matchExistingElementData = [];

        foreach ($feed['fieldUnique'] as $fieldHandle => $value) {
            $mappingInfo = Hash::get($feed['fieldMapping'], $fieldHandle);

            if (!$mappingInfo) {
                continue;
            }

            if (Hash::get($mappingInfo, 'attribute')) {
                $attributeValue = $this->_service->parseAttribute($feedData, $fieldHandle, $mappingInfo);

                if ($attributeValue !== null) {
                    $matchExistingElementData[$fieldHandle] = $attributeValue;
                }
            }

            if (Hash::get($mappingInfo, 'field')) {
                $fieldValue = FeedMe::$plugin->fields->parseField($feed, $element, $feedData, $fieldHandle, $mappingInfo);

                if ($fieldValue !== null) {
                    $matchExistingElementData[$fieldHandle] = $fieldValue;
                }
            }
        }

        FeedMe::info('Match existing element with data `{i}`.', ['i' => json_encode($matchExistingElementData)]);


        //
        // Check for Add/Update/Delete for existing elements
        //

        // Fire an 'onStepBeforeElementMatch' event
        if ($this->hasEventHandlers(self::EVENT_STEP_BEFORE_ELEMENT_MATCH)) {
            $event = new FeedProcessEvent([
                'feed' => $feed,
                'feedData' => $feedData,
                'contentData' => $matchExistingElementData,
            ]);

            $this->trigger(self::EVENT_STEP_BEFORE_ELEMENT_MATCH, $event);

            if (!$event->isValid) {
                return;
            }

            // Allow event to modify variables
            $feed = $event->feed;
            $feedData = $event->feedData;
            $matchExistingElementData = $event->contentData;
        }

        // Check to see if an element already exists
        $existingElement = $this->_service->matchExistingElement($matchExistingElementData, $feed);

        // If there's an existing matching element
        if ($existingElement) {
            FeedMe::info('Existing element [`#{id}`]({url}) found.', ['id' => $existingElement->id, 'url' => $existingElement->cpEditUrl]);

            // If we're deleting or updating an existing element, we want to focus on that one
            if (DuplicateHelper::isUpdate($feed)) {
                $element = clone $existingElement;

                // Update our service with the correct element
                $this->_service->element = $element;
            }

            // There's also a config settings for a field to opt-out of updating. Check against that
            if ($skipUpdateFieldHandle) {
                $updateField = $element->$skipUpdateFieldHandle ?? '';

                // We've got our special field on this element, and its switched on
                if ($updateField === '1') {
                    FeedMe::info('Skipped due to config setting.');

                    return;
                }
            }

            // If we're adding only, and there's an existing element - quit now
            if (DuplicateHelper::isAdd($feed, true)) {
                FeedMe::info('Skipped due to an existing element found, and elements are set to add only.');

                return;
            }
        } else {
            // Have we set to update-only? There are no existing elements, so skip
            if (DuplicateHelper::isUpdate($feed, true)) {
                FeedMe::info('Skipped due to an existing element not found, and elements are set to update only.');

                return;
            }

            // If this variable is explicitly false, this means there's no data in the feed for mapping
            // existing elements - thats a problem no matter which option is selected, so don't proceed.
            // Even if Add is selected, we'll end up with duplicates because it can't find existing elements to skip over
            if ($existingElement === false) {
                FeedMe::info('No existing element mapping data found. Have you ensured you\'ve supplied all correct data in your feed?');

                return;
            }
        }

        // Are we only disabling/deleting only, we need to quit right here
        if (DuplicateHelper::isDisable($feed, true) || DuplicateHelper::isDelete($feed, true)) {
            // If there's an existing element, we want to keep it, otherwise remove it
            if ($existingElement) {
                $processedElementIds[] = $existingElement->id;
            }

            return;
        }


        // 
        // Now, parse all element attributes and custom fields
        //

        // Fire an 'onStepBeforeParseContent' event
        if ($this->hasEventHandlers(self::EVENT_STEP_BEFORE_PARSE_CONTENT)) {
            $event = new FeedProcessEvent([
                'feed' => $feed,
                'feedData' => $feedData,
                'element' => $element,
            ]);

            $this->trigger(self::EVENT_STEP_BEFORE_PARSE_CONTENT, $event);

            if (!$event->isValid) {
                return;
            }

            // Allow event to modify variables
            $feed = $event->feed;
            $feedData = $event->feedData;
            $element = $event->element;
        }

        // Parse the just the element attributes first. We use these in our field contexts, and need a fully-prepped element
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (Hash::get($fieldInfo, 'attribute')) {
                $attributeValue = $this->_service->parseAttribute($feedData, $fieldHandle, $fieldInfo);

                if ($attributeValue !== null) {
                    $attributeData[$fieldHandle] = $attributeValue;
                }
            }
        }

        // Set the attributes for the element
        $element->setAttributes($attributeData, false);

        // Then, do the same for custom fields. Again, this should be done after populating the element attributes
        foreach ($feed['fieldMapping'] as $fieldHandle => $fieldInfo) {
            if (Hash::get($fieldInfo, 'field')) {
                $fieldValue = FeedMe::$plugin->fields->parseField($feed, $element, $feedData, $fieldHandle, $fieldInfo);

                if ($fieldValue !== null) {
                    $fieldData[$fieldHandle] = $fieldValue;
                }
            }
        }

        // Do the same with our custom field data
        $element->setFieldValues($fieldData);

        // Now we've fully prepped our element, one last final check each attribute and field for Twig shorthand to parse
        // We have to do this at the end, separately so we've got full access to the prepped element content
        $parseTwig = FeedMe::$plugin->service->getConfig('parseTwig', $feed['id']);

        if ($parseTwig) {
            foreach ($attributeData as $key => $value) {
                $attributeData[$key] = DataHelper::parseFieldDataForElement($value, $element);
            }

            foreach ($fieldData as $key => $value) {
                $fieldData[$key] = DataHelper::parseFieldDataForElement($value, $element);
            }

            // Set the attributes and fields again
            $element->setAttributes($attributeData, false);
            $element->setFieldValues($fieldData);
        }

        // We need to keep these separate to apply to the element but required when matching against existing elements
        $contentData = $attributeData + $fieldData;


        //
        // It's time to actually save the element!
        //

        // Fire an 'onStepBeforeElementSave' event
        if ($this->hasEventHandlers(self::EVENT_STEP_BEFORE_ELEMENT_SAVE)) {
            $event = new FeedProcessEvent([
                'feed' => $feed,
                'feedData' => $feedData,
                'contentData' => $contentData,
                'element' => $element,
            ]);

            $this->trigger(self::EVENT_STEP_BEFORE_ELEMENT_SAVE, $event);

            if (!$event->isValid) {
                return;
            }

            // Allow event to modify variables
            $feed = $event->feed;
            $feedData = $event->feedData;
            $contentData = $event->contentData;
            $element = $event->element;
        }

        // If we want to check the existing element's content against this new one, let's do it.
        if (FeedMe::$plugin->service->getConfig('compareContent', $feed['id'])) {
            $unchangedContent = DataHelper::compareElementContent($contentData, $existingElement);

            if ($unchangedContent) {
                $info = Craft::t('feed-me', 'Node `#{i}` skipped. No content has changed.', ['i' => ($step + 1)]);

                FeedMe::info($info);
                FeedMe::debug($info);
                FeedMe::debug($contentData);

                $processedElementIds[] = $element->id;

                return;
            }
        }

        FeedMe::info('Data ready to import `{i}`.', ['i' => json_encode($contentData)]);
        FeedMe::debug($contentData);

        // Save the element
        if ($this->_service->save($element, $feed)) {
            // Give elements a chance to perform actions after save
            $this->_service->afterSave($contentData, $feed);

            // Fire an 'onStepElementSave' event
            $event = new FeedProcessEvent([
                'feed' => $feed,
                'feedData' => $feedData,
                'contentData' => $contentData,
                'element' => $element,
            ]);

            $this->trigger(self::EVENT_STEP_AFTER_ELEMENT_SAVE, $event);

            if ($existingElement) {
                FeedMe::info('{name} [`#{id}`]({url}) updated successfully.', ['name' => $this->_service->displayName(), 'id' => $element->id, 'url' => $element->cpEditUrl]);
            } else {
                FeedMe::info('{name} [`#{id}`]({url}) added successfully.', ['name' => $this->_service->displayName(), 'id' => $element->id, 'url' => $element->cpEditUrl]);
            }

            // Store our successfully processed element for feedback in logs, but also in case we're deleting
            $processedElementIds[] = $element->id;

            FeedMe::info('Finished processing of node `#{i}`.', ['i' => ($step + 1)]);

            // Sleep if required
            $sleepTime = FeedMe::$plugin->service->getConfig('sleepTime', $feed['id']);

            if ($sleepTime) {
                sleep($sleepTime);
            }

            return $element;
        } else {
            if ($element->getErrors()) {
                throw new \Exception('Node #' . ($step + 1) . ' - ' . json_encode($element->getErrors()));
            } else {
                throw new \Exception(Craft::t('feed-me', 'Unknown Element saving error occurred.'));
            }
        }
    }

    public function afterProcessFeed($settings, $feed, $processedElementIds)
    {
        if (DuplicateHelper::isDelete($feed) && DuplicateHelper::isDisable($feed)) {
            FeedMe::info("You can't have Delete and Disabled enabled at the same time as an Import Strategy.");
            return;
        }

        $elementsToDeleteDisable = array_diff($settings['existingElements'], $processedElementIds);

        if ($elementsToDeleteDisable) {
            if (DuplicateHelper::isDisable($feed)) {
                $this->_service->disable($elementsToDeleteDisable);
                
                $message = 'The following elements have been disabled: ' . json_encode($elementsToDeleteDisable) . '.';
            } else {
                $this->_service->delete($elementsToDeleteDisable);
                
                $message = 'The following elements have been deleted: ' . json_encode($elementsToDeleteDisable) . '.';
            }

            FeedMe::info($message);
            FeedMe::debug($message);
        }

        // Log the total time taken to process the feed
        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $this->_time_start), 2);

        FeedMe::$stepKey = null;

        $message = 'Processing ' . count($processedElementIds) . ' elements finished in ' . $execution_time . 's';
        FeedMe::info($message);
        FeedMe::debug($message);

        // Fire an 'onProcessFeed' event
        $event = new FeedProcessEvent([
            'feed' => $feed,
        ]);

        $this->trigger(self::EVENT_AFTER_PROCESS_FEED, $event);
    }

    public function debugFeed($feed, $limit, $offset, $processedElementIds)
    {
        $feed->debug = true;

        $feedData = $feed->getFeedData();

        if ($offset) {
            $feedData = array_slice($feedData, $offset);
        }

        if ($limit) {
            $feedData = array_slice($feedData, 0, $limit);
        }

        // Do we even have any data to process?
        if (!$feedData) {
            FeedMe::debug('No feed items to process.');
            return;
        }

        $feedSettings = $this->beforeProcessFeed($feed, $feedData);

        foreach ($feedData as $key => $data) {
            $element = $this->processFeed($key, $feedSettings, $processedElementIds);
        }

        // Check if we need to paginate the feed to run again
        if ($feed->getNextPagination()) {
            $this->debugFeed($feed, null, null, $processedElementIds);
        } else {
            $this->afterProcessFeed($feedSettings, $feed, $processedElementIds);
        }
    }



    // Private Methods
    // =========================================================================

    private function _backupBeforeFeed($feed)
    {
        $logKey = StringHelper::randomString(20);

        $limit = FeedMe::$plugin->service->getConfig('backupLimit', $feed['id']);

        FeedMe::info('Preparing for database backup.', [], ['key' => $logKey]);

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
                    FeedMe::info('Deleting old backup `{i}`.', ['i' => $file], ['key' => $logKey]);

                    FileHelper::unlink($file);
                }
            }
        }

        FeedMe::info('Starting database backup.', [], ['key' => $logKey]);

        $file = $backupPath . '/feedme-' . gmdate('ymd_His') . '_' . strtolower(StringHelper::randomString(10)) . '.sql';

        FeedMe::info('Limit: `{i}` Path: `{j}`.', ['i' => $limit, 'j' => $file], ['key' => $logKey]);

        Craft::$app->getDb()->backupTo($file);

        FeedMe::info('Finished database backup successfully.', [], ['key' => $logKey]);
    }

    // Function to be recursively called to weed out fields that are set to 'noimport'. More complex than usual by the fact
    // that complex fields (Table, Matrix) have multiple fields, some of which aren't mapped. This is why all nested fields
    // should be templated through the 'fields' index, and this function will take care of things from there.
    private function _filterUnmappedFields($fields)
    {
        $return = [];

        if (!is_array($fields)) {
            return $return;
        }

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
