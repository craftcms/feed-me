<?php
namespace Craft;

class FeedMeTask extends BaseTask
{
    private $_feed;
    private $_logsId;
    private $_feedData;
    private $_backup;

    protected function defineSettings()
    {
        return array(
            'feed' => AttributeType::Mixed,
            'items' => AttributeType::Number,
            'logsId' => AttributeType::Number,
        );
    }

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

        // Get the data for the mapping screen, based on the URL provided
        $this->_feedData = craft()->feedMe_feed->getFeed($this->_feed->feedType, $this->_feed->feedUrl, $this->_feed->primaryElement);

        $settings->items = count($this->_feedData);

        // Delete all the entry caches
        craft()->templateCache->deleteCachesByElementType('Entry');

        // Take a step for every row
        return $settings->items;
    }

    public function start() {
        $settings = $this->getSettings();

        // Create a backup before we do anything to the DB
        // Strangely, this hangs the task if in getTotalSteps()
        craft()->db->backup();

        // Create a new Log entry to record logs with
        $this->_logsId = $settings->logsId = craft()->feedMe_logs->start($settings);
    }

    public function runStep($step)
    {
        $settings = $this->getSettings();
        $settings->logsId = $this->_logsId;

        // On start
        if (!$step) {
            $this->start();
        }

        if (isset($this->_feedData[$step])) {
            try {
                // Start our import
                craft()->feedMe_logs->log($settings, Craft::t('Started importing node: ' . $step), LogLevel::Info);
                
                // Do the import
                craft()->feedMe->importNode($step, $this->_feedData[$step], $this->_feed, $settings);

                // If no exception caused above, we've a-okay!
                craft()->feedMe_logs->log($settings, Craft::t('Finished importing node: ' . $step), LogLevel::Info);
            } catch (\Exception $e) {
                craft()->feedMe_logs->log($settings, Craft::t('FeedMeError: ' . $e->getMessage() . '. Check plugin log files for full error.'), LogLevel::Error);
                return false;
            }
        }

        // On finish
        if ($step == ($settings->items - 1)) {
            $this->finish();
        }

        return true;
    }

    public function finish() {
        $settings = $this->getSettings();

        craft()->feedMe_logs->end($settings);
    }
}
