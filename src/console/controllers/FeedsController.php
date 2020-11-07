<?php

namespace craft\feedme\console\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use craft\helpers\Console;
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


    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        $options[] = 'limit';
        $options[] = 'offset';
        return $options;
    }

    /**
     * Queues up feeds to be processed.
     *
     * @param string $feedId A comma-separated list of feed IDs to process
     * @return int
     */
    public function actionQueue($feedId): int
    {
        $ids = explode(',', $feedId);
        $feeds = Plugin::getInstance()->getFeeds();
        $queue = Craft::$app->getQueue();
        $tally = 0;

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $feed = $feeds->getFeedById($id);

                if (!$feed) {
                    $this->stderr("Invalid feed ID: $id" . PHP_EOL, Console::FG_RED);
                    continue;
                }

                $this->stdout('Queuing up feed ');
                $this->stdout($feed->name, Console::FG_CYAN);
                $this->stdout(' ... ');

                $queue->push(new FeedImport([
                    'feed' => $feed,
                    'limit' => $this->limit,
                    'offset' => $this->offset,
                ]));

                $this->stdout('done' . PHP_EOL, Console::FG_GREEN);
                $tally++;
            }
        }

        if ($tally) {
            $this->stdout(($tally === 1 ? '1 feed' : "$tally feeds") . ' queued up to be processed.' . PHP_EOL, Console::FG_GREEN);
        }

        return ExitCode::OK;
    }
}
