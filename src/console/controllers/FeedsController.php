<?php

namespace craft\feedme\console\controllers;

use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\helpers\Console;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * @property Plugin $module
 */
class FeedsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var int|null The total number of feed items to process.
     */
    public ?int $limit = null;

    /**
     * @var int|null The number of items to skip.
     */
    public ?int $offset = null;

    /**
     * @var bool Whether to continue processing a feed (and subsequent pages) if an error occurs.
     * @since 4.3.0
     */
    public bool $continueOnError = false;

    /**
     * @var bool Whether to process all feeds. If this is true, the limit and offset params are ignored.
     */
    public bool $all = false;

    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc
     */
    public function options($actionID): array
    {
        $options = parent::options($actionID);
        $options[] = 'limit';
        $options[] = 'offset';
        $options[] = 'continueOnError';
        $options[] = 'all';
        return $options;
    }

    /**
     * Queues up feeds to be processed.
     *
     * @param string|null $feedId A comma-separated list of feed IDs to process
     * @return int
     */
    public function actionQueue(string $feedId = null): int
    {
        $feeds = Plugin::getInstance()->getFeeds();
        $tally = 0;

        if ($this->all) {
            foreach ($feeds->getFeeds() as $feed) {
                $this->queueFeed($feed, null, null, $this->continueOnError);
                $tally++;
            }
        } elseif ($feedId) {
            $ids = explode(',', $feedId);

            foreach ($ids as $id) {
                $feed = $feeds->getFeedById($id);

                if (!$feed) {
                    $this->stderr("Invalid feed ID: $id" . PHP_EOL, Console::FG_RED);
                    continue;
                }

                $this->queueFeed($feed, $this->limit, $this->offset, $this->continueOnError);
                $tally++;
            }
        }

        if ($tally) {
            $this->stdout(($tally === 1 ? '1 feed' : "$tally feeds") . ' queued up to be processed.' . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stdout('Could not determine the feeds to process.' . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }

    /**
     * Push a feed to the queue to be processed.
     *
     * @param      $feed
     * @param null $limit
     * @param null $offset
     * @param bool $continueOnError
     */
    protected function queueFeed($feed, $limit = null, $offset = null, bool $continueOnError = false): void
    {
        $this->stdout('Queuing up feed ');
        $this->stdout($feed->name, Console::FG_CYAN);
        $this->stdout(' ... ');

        $this->module->queue->push(new FeedImport([
            'feed' => $feed,
            'limit' => $limit,
            'offset' => $offset,
            'continueOnError' => $continueOnError,
        ]));

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
    }
}
