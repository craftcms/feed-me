<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\feedme\base\ElementInterface;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\helpers\DataHelper;
use craft\feedme\helpers\DuplicateHelper;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\helpers\App;
use craft\helpers\FileHelper;
use craft\helpers\StringHelper;

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

    /**
     * @var
     */
    private $_time_start;

    /**
     * @var ElementInterface
     */
    private $_service;

    /**
     * @var array
     */
    private $_data;


    // Public Methods
    // =========================================================================

    /**
     * @param FeedModel $feed
     * @param array $feedData
     * @return array|void
     * @throws \Exception
     */
    public function beforeProcessFeed($feed, $feedData)
    {
        Plugin::$feedName = $feed->name;

        Plugin::info('Preparing for feed processing.');

        if (!$feedData) {
            throw new \Exception(Craft::t('feed-me', 'No data to import.'));
        }

        // Check for backup, best to do this before we do anything
        if ($feed->backup) {
            $this->_backupBeforeFeed($feed);
        }

        $runGcBeforeFeed = Plugin::$plugin->service->getConfig('runGcBeforeFeed', $feed['id']);

        if ($runGcBeforeFeed) {
            $gc = Craft::$app->getGc();
            $gc->deleteAllTrashed = true;
            $gc->run(true);
        }

        $this->_data = $feedData;
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
        if (!empty($feed['fieldUnique'])) {
            foreach ($feed['fieldUnique'] as $key => $value) {
                if ((int)$value === 1) {
                    $return['fieldUnique'][$key] = $value;
                }
            }
        }

        if (empty($feed['singleton']) && empty($return['fieldUnique'])) {
            throw new \Exception(Craft::t('feed-me', 'No unique fields checked.'));
        }

        // Get the service for the Element Type we're dealing with
        if (!$feed->element) {
            throw new \Exception(Craft::t('feed-me', 'Unknown Element Type Service called.'));
        }

        // If our duplication handling is to delete - we delete all elements
        // If our duplication handling is to disable - we disable all elements
        if (
            DuplicateHelper::isDelete($feed) ||
            DuplicateHelper::isDisable($feed) ||
            DuplicateHelper::isDisableForSite($feed))
        {
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

        $this->_data = array_values($this->_data);

        // Fire an 'onBeforeProcessFeed' event
        $event = new FeedProcessEvent([
            'feed' => $feed,
            'feedData' => $this->_data,
        ]);

        $this->trigger(self::EVENT_BEFORE_PROCESS_FEED, $event);

        if (!$event->isValid) {
            return;
        }

        // Allow event to modify the feed data
        $this->_data = $event->feedData;

        // Return the feed data
        $return['feedData'] = $this->_data;

        Plugin::info('Finished preparing for feed processing.');

        return $return;
    }

    /**
     * @param $step
     * @param $feed
     * @param $processedElementIds
     * @param $feedData
     * @return mixed|void
     * @throws \Exception
     */
    public function processFeed($step, $feed, &$processedElementIds, $feedData = null)
    {
        $attributeData = [];
        $fieldData = [];

        // We can opt-out of updating certain elements if a field is switched on
        $skipUpdateFieldHandle = Plugin::$plugin->service->getConfig('skipUpdateFieldHandle', $feed['id']);

        //
        // Lets get started!
        //

        $logKey = StringHelper::randomString(20);

        // Save this to session so we don't have to pass it around everywhere.
        Plugin::$stepKey = $logKey;

        // Try to fix an elusive bug...
        if (!is_numeric($step)) {
            Plugin::error('Error `{i}`.', ['i' => json_encode($step)]);
        }

        if (!is_array($this->_data) || empty($this->_data[0])) {
            Plugin::info('There is no data in the feed to process.');
            return;
        }

        Plugin::info('Starting processing of node `#{i}`.', ['i' => ($step + 1)]);

        // Set up a model for this Element Type
        $element = $this->_service->setModel($feed);

        // From the raw data in our feed, we need to fix it up so its Craft-ready for the element and fields
        $feedData = $feedData ?? $this->_data[$step];

        // We need to first find a potentially existing element, and to do that, we need to prep just the fields
        // that are selected as the unique identifier. We prep everything else later on.
        $matchExistingElementData = [];

        if (empty($feed['singleton'])) {
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
                    $fieldValue = Plugin::$plugin->fields->parseField($feed, $element, $feedData, $fieldHandle, $mappingInfo);

                    if ($fieldValue !== null) {
                        $matchExistingElementData[$fieldHandle] = $fieldValue;
                    }
                }
            }

            Plugin::info('Match existing element with data `{i}`.', ['i' => json_encode($matchExistingElementData)]);
        }


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
            Plugin::info('Existing element [`#{id}`]({url}) found.', ['id' => $existingElement->id, 'url' => $existingElement->cpEditUrl]);

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
                    Plugin::info('Skipped due to config setting.');

                    return;
                }
            }

            // If we're adding only, and there's an existing element - quit now
            if (DuplicateHelper::isAdd($feed, true)) {
                Plugin::info('Skipped due to an existing element found, and elements are set to add only.');

                return;
            }
        } else {
            // Have we set to update-only? There are no existing elements, so skip
            if (DuplicateHelper::isUpdate($feed, true)) {
                Plugin::info('Skipped due to an existing element not found, and elements are set to update only.');

                return;
            }

            // If this variable is explicitly false, this means there's no data in the feed for mapping
            // existing elements - that's a problem no matter which option is selected, so don't proceed.
            // Even if Add is selected, we'll end up with duplicates because it can't find existing elements to skip over
            if ($existingElement === false) {
                Plugin::info('No existing element mapping data found. Have you ensured you\'ve supplied all correct data in your feed?');

                return;
            }
        }

        // Are we only disabling/deleting only, we need to quit right here
        // https://github.com/craftcms/feed-me/issues/696
        if (
            DuplicateHelper::isDisable($feed, true) ||
            DuplicateHelper::isDisableForSite($feed, true) ||
            DuplicateHelper::isDelete($feed, true) ||
            (!DuplicateHelper::isUpdate($feed) && $existingElement)
        ) {
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
                $fieldValue = Plugin::$plugin->fields->parseField($feed, $element, $feedData, $fieldHandle, $fieldInfo);

                if ($fieldValue !== null) {
                    $fieldData[$fieldHandle] = $fieldValue;
                }
            }
        }

        // Do the same with our custom field data
        $element->setFieldValues($fieldData);

        // Now we've fully prepped our element, one last final check each attribute and field for Twig shorthand to parse
        // We have to do this at the end, separately so we've got full access to the prepped element content
        $parseTwig = Plugin::$plugin->service->getConfig('parseTwig', $feed['id']);

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
        if (Plugin::$plugin->service->getConfig('compareContent', $feed['id'])) {
            $unchangedContent = DataHelper::compareElementContent($contentData, $existingElement);

            if ($unchangedContent) {
                $info = Craft::t('feed-me', 'Node `#{i}` skipped. No content has changed.', ['i' => ($step + 1)]);

                Plugin::info($info);
                Plugin::debug($info);
                Plugin::debug($contentData);

                $processedElementIds[] = $element->id;

                return;
            }
        }

        Plugin::info('Data ready to import `{i}`.', ['i' => json_encode($contentData)]);
        Plugin::debug($contentData);

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
                Plugin::info('{name} [`#{id}`]({url}) updated successfully.', ['name' => $this->_service::displayName(), 'id' => $element->id, 'url' => $element->cpEditUrl]);
            } else {
                Plugin::info('{name} [`#{id}`]({url}) added successfully.', ['name' => $this->_service::displayName(), 'id' => $element->id, 'url' => $element->cpEditUrl]);
            }

            // Store our successfully processed element for feedback in logs, but also in case we're deleting
            $processedElementIds[] = $element->id;

            Plugin::info('Finished processing of node `#{i}`.', ['i' => ($step + 1)]);

            // Sleep if required
            $sleepTime = Plugin::$plugin->service->getConfig('sleepTime', $feed['id']);

            if ($sleepTime) {
                sleep($sleepTime);
            }

            return $element;
        }

        if ($element->getErrors()) {
            throw new \Exception('Node #' . ($step + 1) . ' - ' . json_encode($element->getErrors()));
        }

        throw new \Exception(Craft::t('feed-me', 'Unknown Element saving error occurred.'));
    }

    /**
     * @param $settings
     * @param $feed
     * @param $processedElementIds
     */
    public function afterProcessFeed($settings, $feed, $processedElementIds)
    {
        if ((int)DuplicateHelper::isDelete($feed) + (int)DuplicateHelper::isDisable($feed) + (int)DuplicateHelper::isDisableForSite($feed) > 1) {
            Plugin::info("You can't have Delete and Disabled enabled at the same time as an Import Strategy.");
            return;
        }

        if (DuplicateHelper::isDisableForSite($feed) && !$feed->siteId) {
            Plugin::info('You can’t choose “Disable missing elements in the target site” for feeds without a target site.');
            return;
        }

        $elementsToDeleteDisable = array_diff($settings['existingElements'], $processedElementIds);

        if ($elementsToDeleteDisable) {
            if (DuplicateHelper::isDisable($feed)) {
                $this->_service->disable($elementsToDeleteDisable);
                $message = 'The following elements have been disabled: ' . json_encode($elementsToDeleteDisable) . '.';
            } else if (DuplicateHelper::isDisableForSite($feed)) {
                $this->_service->disableForSite($elementsToDeleteDisable);
                $message = 'The following elements have been disabled for the target site: ' . json_encode($elementsToDeleteDisable) . '.';
            } else {
                $this->_service->delete($elementsToDeleteDisable);
                $message = 'The following elements have been deleted: ' . json_encode($elementsToDeleteDisable) . '.';
            }

            Plugin::info($message);
            Plugin::debug($message);
        }

        // Log the total time taken to process the feed
        $time_end = microtime(true);
        $execution_time = number_format(($time_end - $this->_time_start), 2);

        Plugin::$stepKey = null;

        $message = 'Processing ' . count($processedElementIds) . ' elements finished in ' . $execution_time . 's';
        Plugin::info($message);
        Plugin::debug($message);

        // Fire an 'onProcessFeed' event
        $event = new FeedProcessEvent([
            'feed' => $feed,
        ]);

        $this->trigger(self::EVENT_AFTER_PROCESS_FEED, $event);
    }

    /**
     * @param $feed
     * @param $limit
     * @param $offset
     * @param $processedElementIds
     * @throws \Exception
     */
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
            Plugin::debug('No feed items to process.');
            return;
        }

        $feedSettings = $this->beforeProcessFeed($feed, $feedData);

        foreach ($feedData as $key => $data) {
            $this->processFeed($key, $feedSettings, $processedElementIds);
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

    /**
     * @param $feed
     * @throws \craft\errors\ShellCommandException
     * @throws \yii\base\Exception
     */
    private function _backupBeforeFeed($feed)
    {
        $logKey = StringHelper::randomString(20);

        $limit = Plugin::$plugin->service->getConfig('backupLimit', $feed['id']);

        Plugin::info('Preparing for database backup.', [], ['key' => $logKey]);

        $backupPath = Craft::$app->getPath()->getDbBackupPath();

        if (is_dir($backupPath)) {
            // Check for any existing backups, if more than our limit, we need to kill some off...
            $currentBackups = FileHelper::findFiles($backupPath, [
                'only' => ['feedme-*.sql'],
                'recursive' => false
            ]);

            // Remove all the previous backups, except the amount we want to limit
            $backupsToDelete = [];

            if (is_array($currentBackups) && count($currentBackups) > $limit) {
                $backupsToDelete = array_splice($currentBackups, 0, (count($currentBackups) - $limit));
            }

            // If we have any to remove, lets delete them
            if (count($backupsToDelete)) {
                foreach ($backupsToDelete as $file) {
                    Plugin::info('Deleting old backup `{i}`.', ['i' => $file], ['key' => $logKey]);

                    FileHelper::unlink($file);
                }
            }
        }

        Plugin::info('Starting database backup.', [], ['key' => $logKey]);

        $file = $backupPath . '/feedme-' . gmdate('ymd_His') . '_' . strtolower(StringHelper::randomString(10)) . '.sql';

        Plugin::info('Limit: `{i}` Path: `{j}`.', ['i' => $limit, 'j' => $file], ['key' => $logKey]);

        Craft::$app->getDb()->backupTo($file);

        Plugin::info('Finished database backup successfully.', [], ['key' => $logKey]);
    }

    /**
     * Function to weed out fields that are set to 'noimport'. More complex than usual by the fact
     * that complex fields (Table, Matrix) have multiple fields, some of which aren't mapped. This is why all nested fields
     * should be templated through the 'fields' index, and this function will take care of things from there.
     *
     * @param $fields
     * @return array
     */
    private function _filterUnmappedFields($fields)
    {
        if (!is_array($fields)) {
            return [];
        }

        // Find any items like `[title.node] => noimport` and remove the outer field info. Slightly complicated
        // for nested block/fields, and if I was better at recursion, this could be more elegant, but loop through a
        // bunch of times, removing stuff as we go, starting at the inner nested level. Each loop will remove more levels
        // of un-mapped nodes
        for ($i = 0; $i < 5; $i++) {
            foreach (Hash::flatten($fields) as $key => $value) {
                $explode = explode('.', $key);
                $lastIndex = array_pop($explode);
                $infoPath = implode('.', $explode);

                $node = Hash::get($fields, $infoPath . '.node');

                if ($lastIndex === 'node' && $value === 'noimport') {
                    $fields = Hash::remove($fields, $infoPath);
                }

                if ($lastIndex === 'fields' && empty($value)) {
                    // Remove any empty field definitions - but only if there's no node mapping.
                    // This is the case when mapping a value to entries, but not mapping any of its inner element fields.
                    // We want to retain the mapping to the outer field, but ditch any inner fields not mapped
                    if ($node) {
                        $fields = Hash::remove($fields, $infoPath . '.fields');
                    } else {
                        $fields = Hash::remove($fields, $infoPath);
                    }
                }

                if ($lastIndex === 'blocks' && empty($value)) {
                    $fields = Hash::remove($fields, $infoPath);
                }
            }
        }

        return $fields;
    }
}
