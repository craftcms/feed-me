<?php
namespace verbb\feedme\queue\jobs;

use verbb\feedme\FeedMe;

use Craft;
use craft\queue\BaseJob;

class FeedImport extends BaseJob
{
    // Properties
    // =========================================================================

    public $feed;
    public $limit;
    public $offset;


    // Public Methods
    // =========================================================================

    public function execute($queue)
    {
        $errors = [];

        try {
            $feedData = $this->feed->getFeedData();

            if ($this->offset) {
                $feedData = array_slice($feedData, $this->offset);
            }

            if ($this->limit) {
                $feedData = array_slice($feedData, 0, $this->limit);
            }

            $totalSteps = count($feedData);

            // Do we even have any data to process?
            if (!$totalSteps) {
                FeedMe::info($this->feed, 'No feed items to process.');
                return;
            }

            $feedSettings = FeedMe::$plugin->process->beforeProcessFeed($this->feed, $feedData);

            foreach ($feedData as $key => $data) {
                $element = FeedMe::$plugin->process->processFeed($key, $feedSettings);

                $this->setProgress($queue, $key++ / $totalSteps);
            }

            FeedMe::$plugin->process->afterProcessFeed($feedSettings, $this->feed);
        } catch (\Throwable $e) {
            FeedMe::error($this->feed, $e->getMessage() . ' - ' . basename($e->getFile()) . ':' . $e->getLine());
        }
    }


    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t('feed-me', 'Running {name} feed.', [ 'name' => $this->feed->name ]);
    }
}
