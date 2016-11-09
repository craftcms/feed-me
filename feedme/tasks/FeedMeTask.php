<?php
namespace Craft;

class FeedMeTask extends BaseTask
{
    // Properties
    // =========================================================================

    private $_feed;
    private $_feedData;
    private $_feedSettings;

    // Public Methods
    // =========================================================================

    public function getDescription()
    {
        return Craft::t('Processing feed');
    }

    public function getTotalSteps()
    {
        try {
            // Get settings
            $settings = $this->getSettings();

            // Get the Feed
            $this->_feed = $settings->feed;

            // Get the data for the mapping screen, based on the URL provided
            $this->_feedData = craft()->feedMe_data->getFeed($this->_feed->feedType, $this->_feed->feedUrl, $this->_feed->primaryElement, $this->_feed);

            // There are also a few once-off things we can do for this feed to assist with processing.
            $this->_feedSettings = craft()->feedMe_process->setupForProcess($this->_feed, $this->_feedData);

        } catch (\Exception $e) {
            FeedMePlugin::log($this->_feed->name . ': ' . $e->getMessage(), LogLevel::Error, true);

            return 0;
        }

        // Take a step for every row
        return count($this->_feedData);
    }

    public function runStep($step)
    {
        // Do we even have any data to process?
        if (!$this->getTotalSteps()) {
            return true;
        }

        try {
            $settings = $this->getSettings();

            // On the first run of the feed
            if (!$step) {
                // Fire an "onBeforeProcessFeed" event
                $event = new Event($this, array('settings' => $this->_feedSettings));
                craft()->feedMe_process->onBeforeProcessFeed($event);
            }

            // Process each feed node
            if (isset($this->_feedData[$step])) {
                craft()->feedMe_process->processFeed($step, $this->_feedSettings);
            } else {
                FeedMePlugin::log($this->_feed->name . ': FeedMeError', LogLevel::Error, true);
            }

            // When finished
            if ($step == ($this->getTotalSteps() - 1)) {
                craft()->feedMe_process->finalizeAfterProcess($this->_feedSettings, $this->_feed);

                // Fire an "onProcessFeed" event
                $event = new Event($this, array('settings' => $this->_feedSettings));
                craft()->feedMe_process->onProcessFeed($event);
            }
        } catch (\Exception $e) {
            FeedMePlugin::log($this->_feed->name . ': ' . $e->getMessage(), LogLevel::Error, true);

            return false;
        }

        return true;
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