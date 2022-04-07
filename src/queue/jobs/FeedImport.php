<?php

namespace craft\feedme\queue\jobs;

use Craft;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\queue\BaseJob;
use Throwable;
use yii\queue\RetryableJobInterface;

/**
 *
 * @property-read mixed $ttr
 */
class FeedImport extends BaseJob implements RetryableJobInterface
{
    // Properties
    // =========================================================================

    /**
     * @var FeedModel
     */
    public FeedModel $feed;

    /**
     * @var int|null
     */
    public ?int $limit = null;

    /**
     * @var int|null
     */
    public ?int $offset = null;

    /**
     * @var array|null
     */
    public ?array $processedElementIds = null;

    /**
     * @var bool Whether to continue processing a feed (and subsequent pages) if an error occurs
     * @since 4.3.0
     */
    public bool $continueOnError = true;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return Plugin::$plugin->getSettings()->queueTtr ?? Plugin::getInstance()->queue->ttr;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error): bool
    {
        $attempts = Plugin::$plugin->getSettings()->queueMaxRetry ?? Plugin::getInstance()->queue->attempts;
        return $attempt < $attempts;
    }

    /**
     * @inheritDoc
     */
    public function execute($queue): void
    {
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
                Plugin::info('No feed items to process.');
                return;
            }

            $feedSettings = Plugin::$plugin->process->beforeProcessFeed($this->feed, $feedData);

            $feedData = $feedSettings['feedData'];

            $totalSteps = count($feedData);

            $index = 0;

            foreach ($feedData as $data) {
                try {
                    Plugin::$plugin->process->processFeed($index, $feedSettings, $this->processedElementIds);
                } catch (Throwable $e) {
                    if (!$this->continueOnError) {
                        throw $e;
                    }

                    // We want to catch any issues in each iteration of the loop (and log them), but this allows the
                    // rest of the feed to continue processing.
                    Plugin::error('`{e} - {f}: {l}`.', ['e' => $e->getMessage(), 'f' => basename($e->getFile()), 'l' => $e->getLine()]);
                    Craft::$app->getErrorHandler()->logException($e);
                }

                $this->setProgress($queue, $index++ / $totalSteps);
            }

            // Check if we need to paginate the feed to run again
            if ($this->feed->getNextPagination()) {
                Plugin::getInstance()->queue->push(new self([
                    'feed' => $this->feed,
                    'limit' => $this->limit,
                    'offset' => $this->offset,
                    'processedElementIds' => $this->processedElementIds,
                ]));
            } else {
                // Only perform the afterProcessFeed function after any/all pagination is done
                Plugin::$plugin->process->afterProcessFeed($feedSettings, $this->feed, $this->processedElementIds);
            }
        } catch (Throwable $e) {
            // Even though we catch errors on each step of the loop, make sure to catch errors that can be anywhere
            // else in this function, just to be super-safe and not cause the queue job to die.
            Plugin::error('`{e} - {f}: {l}`.', ['e' => $e->getMessage(), 'f' => basename($e->getFile()), 'l' => $e->getLine()]);
            Craft::$app->getErrorHandler()->logException($e);
        }
    }


    // Protected Methods
    // =========================================================================

    /**
     * @return string
     */
    protected function defaultDescription(): string
    {
        return Craft::t('feed-me', 'Running {name} feed.', ['name' => $this->feed->name]);
    }
}
