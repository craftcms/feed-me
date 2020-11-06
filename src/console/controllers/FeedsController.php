<?php

namespace craft\feedme\console\controllers;

use Craft;
use craft\feedme\Plugin;
use craft\feedme\queue\jobs\FeedImport;
use yii\console\Controller;

class FeedsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    public $limit;

    /**
     * @var
     */
    public $offset;


    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        return ['limit', 'offset'];
    }

    /**
     * Processes a feed(s)
     *
     * @param string $feedId A comma-separated list of feed IDs to process
     * @return bool
     */
    public function actionRun($feedId)
    {
        $ids = explode(',', $feedId);

        if (is_array($ids)) {
            foreach ($ids as $id) {
                $feed = Plugin::$plugin->feeds->getFeedById($id);

                if (!$feed) {
                    echo "No feed found with an ID of $id\n";
                    continue;
                }

                $processedElementIds = [];

                echo "Feed processing started for feed ID $id\n";

                Craft::$app->getQueue()->delay(0)->push(new FeedImport([
                    'feed' => $feed,
                    'limit' => $this->limit,
                    'offset' => $this->offset,
                    'processedElementIds' => $processedElementIds,
                ]));

                Craft::$app->getQueue()->run();
            }
        }

        return true;
    }

}
