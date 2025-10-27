<?php

namespace craft\feedme\console\controllers;

use craft\console\Controller;
use craft\feedme\models\FeedModel;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\feedme\records\SequencesRecord;
use craft\helpers\Console;
use craft\helpers\DateTimeHelper;
use craft\helpers\Db;
use craft\helpers\Json;
use craft\helpers\Queue;
use craft\helpers\StringHelper;
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

        $key = StringHelper::randomString(10);
        $options = Json::encode([
            'limit' => $this->limit,
            'offset' => $this->offset,
            'continueOnError' => $this->continueOnError,
        ]);
        $timestamp = Db::prepareDateForDb(DateTimeHelper::now());

        if ($this->all) {
            foreach ($feeds->getFeeds() as $feed) {
                $this->addFeedToSequence($feed,$key, $options, $timestamp);
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

                $this->addFeedToSequence($feed, $key, $options, $timestamp);
                $tally++;
            }
        }

        if ($tally) {
            $this->stdout(($tally === 1 ? '1 feed' : "$tally feeds") . ' queued up to be processed.' . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stdout('Could not determine the feeds to process.' . PHP_EOL, Console::FG_GREEN);
        }

        $this->queueFirstFeed($key);

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
        Queue::push(new FeedImport([
            'feed' => $feed,
            'limit' => $limit,
            'offset' => $offset,
            'continueOnError' => $continueOnError,
        ]));
    }

    /**
     * Add feeds to a sequence, so that they're processed in order in which they were provided.
     *
     * @param FeedModel $feed
     * @param string $key
     * @param string $options
     * @param string $timestamp
     * @return void
     * @throws \yii\db\Exception
     */
    protected function addFeedToSequence(FeedModel $feed, string $key, string $options, string $timestamp): void
    {
        $this->stdout('Queuing up feed ');
        $this->stdout($feed->name, Console::FG_CYAN);
        $this->stdout(' ... ');

        Db::insert('{{%feedme_sequences}}', [
            'feedId' => $feed->id,
            'key' => $key,
            'options' => $options,
            'timestamp' => $timestamp,
        ]);

        $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
    }

    /**
     * Queue first feed from a sequence.
     *
     * @param string $key
     * @return void
     * @throws \yii\base\InvalidConfigException
     */
    protected function queueFirstFeed(string $key): void
    {
        $firstFeed = SequencesRecord::find()->select(['feedId', 'options'])->where(['key' => $key])->one();
        $id = $firstFeed['feedId'];
        $options = Json::decode($firstFeed['options']);

        $feed = Plugin::getInstance()->getFeeds()->getFeedById($id);

        if (!$feed) {
            $this->stderr("Invalid feed ID: $id" . PHP_EOL, Console::FG_RED);
        }

        $this->queueFeed($feed, $options['limit'], $options['offset'], $options['continueOnError']);
    }
}
