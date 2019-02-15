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
    public $processedElementIds;


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

            // Do we even have any data to process?
            if (!$feedData) {
                FeedMe::info('No feed items to process.');
                return;
            }

            $totalSteps = count($feedData);

            $feedSettings = FeedMe::$plugin->process->beforeProcessFeed($this->feed, $feedData);

            $index = 0;
            
            foreach ($feedData as $key => $data) {
                try {
                    $element = FeedMe::$plugin->process->processFeed($index, $feedSettings, $this->processedElementIds);
                } catch (\Throwable $e) {
                    // We want to catch any issues in each iteration of the loop (and log them), but this allows the
                    // rest of the feed to continue processing.
                    FeedMe::error('`{e} - {f}: {l}`.', ['e' => $e->getMessage(), 'f' => basename($e->getFile()), 'l' => $e->getLine()]);
                }

                $this->setProgress($queue, $index++ / $totalSteps);
            }

            // Check if we need to paginate the feed to run again
            if ($this->feed->getNextPagination()) {
                Craft::$app->getQueue()->delay(0)->push(new FeedImport([
                    'feed' => $this->feed,
                    'limit' => $this->limit,
                    'offset' => $this->offset,
                    'processedElementIds' => $this->processedElementIds,
                ]));
            } else {
                // Only perform the afterProcessFeed function after any/all pagination is done
                FeedMe::$plugin->process->afterProcessFeed($feedSettings, $this->feed, $this->processedElementIds);
            }
        } catch (\Throwable $e) {
            // Even though we catch errors on each step of the loop, make sure to catch errors that can be anywhere
            // else in this function, just to be super-safe and not cause the queue job to die.
            FeedMe::error('`{e} - {f}: {l}`.', ['e' => $e->getMessage(), 'f' => basename($e->getFile()), 'l' => $e->getLine()]);
        }
    }


    // Protected Methods
    // =========================================================================

    protected function defaultDescription(): string
    {
        return Craft::t('feed-me', 'Running {name} feed.', [ 'name' => $this->feed->name ]);
    }
}
