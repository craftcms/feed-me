<?php

namespace craft\feedme\queue\jobs;

use Cake\Utility\Hash;
use Craft;
use craft\base\Batchable;
use craft\feedme\datatypes\DataBatcher;
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\services\Process;
use craft\helpers\Queue;
use craft\queue\BaseBatchedJob;
use Throwable;
use yii\queue\RetryableJobInterface;

/**
 *
 * @property-read mixed $ttr
 */
class FeedImport extends BaseBatchedJob implements RetryableJobInterface
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

    /**
     * @var mixed The Unix timestamp with microseconds of when the feed import started being processed
     * @since 5.11.0
     */
    public mixed $startTime = null;

    /**
     * @var array The Feed's settings as prepared by beforeProcessFeed()
     */
    private array $_feedSettings = [];

    /**
     * @var int The index of currently processed item in current batch
     */
    private int $_index = 0;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function getTtr()
    {
        return Plugin::$plugin->service->getConfig('queueTtr', $this->feed->id) ?? Plugin::getInstance()->queue->ttr;
    }

    /**
     * @inheritDoc
     */
    public function canRetry($attempt, $error): bool
    {
        $attempts = Plugin::$plugin->service->getConfig('queueMaxRetry', $this->feed->id) ?? Plugin::getInstance()->queue->attempts;
        return $attempt < $attempts;
    }

    /**
     * @inheritdoc
     */
    protected function loadData(): Batchable
    {
        $feedData = $this->feed->getFeedData();

        if ($this->offset) {
            $feedData = array_slice($feedData, $this->offset);
        }

        if ($this->limit) {
            $feedData = array_slice($feedData, 0, $this->limit);
        }

        $data = $feedData;

        // Our main data-parsing function. Handles the actual data values, defaults and field options
        foreach ($feedData as $key => $nodeData) {
            if (!is_array($nodeData)) {
                $nodeData = [$nodeData];
            }

            $data[$key] = Hash::flatten($nodeData, '/');
        }

        $data = array_values($data);

        // Fire an 'onBeforeProcessFeed' event
        $event = new FeedProcessEvent([
            'feed' => $this->feed,
            'feedData' => $data,
        ]);

        Plugin::$plugin->process->trigger(Process::EVENT_BEFORE_PROCESS_FEED, $event);

        if (!$event->isValid) {
            return new DataBatcher([]);
        }

        // Allow event to modify the feed data
        $data = $event->feedData;

        return new DataBatcher($data);
    }

    /**
     * @inheritdoc
     */
    protected function processItem(mixed $item): void
    {
        try {
            Plugin::$plugin->process->processFeed($this->_index, $this->_feedSettings, $this->processedElementIds, $item, $this->batchIndex);
        } catch (Throwable $e) {
            if (!$this->continueOnError) {
                throw $e;
            }

            // We want to catch any issues in each iteration of the loop (and log them), but this allows the
            // rest of the feed to continue processing.
            Plugin::error('`{e} - {f}: {l}`.', ['e' => $e->getMessage(), 'f' => basename($e->getFile()), 'l' => $e->getLine()]);
            Craft::$app->getErrorHandler()->logException($e);
        }

        $this->_index++;
    }
    /**
     * @inheritDoc
     */
    public function execute($queue): void
    {
        $processService = Plugin::$plugin->getProcess();
        if ($this->itemOffset == 0) {
            $processService->beforeProcessFeed($this->feed, (array)$this->data());
        }

        if (!$this->startTime) {
            $this->startTime = $processService->time_start;
        }

        if (empty($this->_feedSettings)) {
            $this->_feedSettings = $processService->getFeedSettings($this->feed, (array)$this->data());
        }

        parent::execute($queue);

        // Check if we need to paginate the feed to run again
        if ($this->itemOffset == $this->totalItems()) {
            if ($this->feed->getNextPagination()) {
                Queue::push(new self([
                    'feed' => $this->feed,
                    'limit' => $this->limit,
                    'offset' => $this->offset,
                    'processedElementIds' => $this->processedElementIds,
                    'startTime' => $this->startTime,
                ]));
            } else {
                // Only perform the afterProcessFeed function after any/all pagination is done
                $processService->afterProcessFeed($this->_feedSettings, $this->feed, $this->processedElementIds, $this->startTime);
            }
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
