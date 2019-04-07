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

    public $id;
    public $limit;
    public $offset;


    // Public Methods
    // =========================================================================

    public function options($actionID): array
    {
        return ['id', 'limit', 'offset'];
    }

    public function actionRun()
    {
        $ids = explode(',', $this->id);

        foreach ($ids as $id) {
            echo "Feed processing started for Feed ID $id\n";

            $feed = Plugin::$plugin->feeds->getFeedById($id);

            $processedElementIds = [];

            Craft::$app->getQueue()->delay(0)->push(new FeedImport([
                'feed' => $feed,
                'limit' => $this->limit,
                'offset' => $this->offset,
                'processedElementIds' => $processedElementIds,
            ]));

            Craft::$app->getQueue()->run();
        }

        return true;
    }

}
