<?php

namespace craft\feedme\console\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\helpers\Console;
use craft\queue\Queue;
use yii\base\Module;
use yii\console\Controller;
use yii\console\ExitCode;

class FeedsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var int|null The total number of feed items to process
     */
    public $limit;

    /**
     * @var int|null The number of items to skip
     */
    public $offset;

    /**
     * @var bool Whether to continue processing a feed (and subsequent pages) if an error occurs
     * @since 4.3.0
     */
    public $continueOnError = false;

    /**
     * @var bool Whether to process all feeds
     */
    public $all = false;

    /**
     * @var Queue The queue processing jobs
     */
    protected $queue;

    // Public Methods
    // =========================================================================

    /**
     * @param string $id the ID of this controller.
     * @param Module $module the module that this controller belongs to.
     * @param array $config name-value pairs that will be used to initialize the object properties.
     */
    public function __construct($id, $module, $config = [])
    {
        $this->queue = Craft::$app->getQueue();

        parent::__construct($id, $module, $config);
    }

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
    public function actionQueue($feedId = null): int
    {
        $feeds = Plugin::getInstance()->getFeeds();
        $tally = 0;

        if ($this->all) {
            foreach($feeds->getFeeds() as $feed) {
                $this->queueFeed($feed, null, null, $this->continueOnError);

                $tally++;
            }
        }

        if (! $this->all && $feedId) {
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
    protected function queueFeed($feed, $limit = null, $offset = null, $continueOnError = false): void
    {
        $this->stdout('Queuing up feed ');
        $this->stdout($feed->name, Console::FG_CYAN);
        $this->stdout(' ... ');

        $this->queue->push(new FeedImport([
            'feed' => $feed,
            'limit' => $limit,
            'offset' => $offset,
            'continueOnError' => $continueOnError,
        ]));

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
    }
}
