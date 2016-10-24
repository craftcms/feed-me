<?php
namespace Craft;

class FeedMeTask extends BaseTask
{
    // Properties
    // =========================================================================

    private $_feed;
    private $_logsId;
    private $_feedData;
    private $_feedSettings;
    private $_backup;
    private $_chunkedFeedData;
    private $_processedEntries = array();

    // Public Methods
    // =========================================================================

    public function getDescription()
    {
        return Craft::t('Processing feed');
    }

    public function getTotalSteps()
    {
        // Get settings
        $settings = $this->getSettings();

        // Get the Feed
        $this->_feed = $settings->feed;

        // There are also a few once-off things we can do for this feed to assist with processing.
        $this->_feedSettings = craft()->feedMe->setupForImport($this->_feed);

        // Get the data for the mapping screen, based on the URL provided
        $this->_feedData = craft()->feedMe_feed->getFeed($this->_feed->feedType, $this->_feed->feedUrl, $this->_feed->primaryElement);

        if (!$this->_feedData) {
            FeedMePlugin::log($this->_feed->name . ': FeedMeError', LogLevel::Error, true);
            return false;
        }

        // Chunk the feed data into chunks of 100 - optimises mapping process by not calling service each step
        $this->_chunkedFeedData = array_chunk($this->_feedData, 100);

        // Delete all the entry caches
        craft()->templateCache->deleteCachesByElementType('Entry');

        // Create a backup before we do anything to the DB
        if ($this->_feed->backup) {
            $backup = craft()->db->backup();
        }

        // Take a step for every row
        return count($this->_chunkedFeedData);
    }

    public function runStep($step)
    {
        $result = craft()->feedMe->importNode($this->_chunkedFeedData[$step], $this->_feed, $this->_feedSettings);
        $this->_processedEntries = array_merge($this->_processedEntries, $result['processedEntries']);

        // For delete, at the end of our processing, we delete all entries not recorded
        if ($step == $this->getTotalSteps()-1) {
            craft()->feedMe->deleteLeftoverEntries($this->_feedSettings, $this->_feed, $this->_processedEntries, $result);
        }

        if (!$result['result']) {
            return 'Feed Me Failure: Check Feed Me logs.';
        } else {
            return true;
        }
    }

    // Protected Methods
    // =========================================================================

    protected function defineSettings()
    {
        return array(
            'feed' => AttributeType::Mixed,
        );
    }
}