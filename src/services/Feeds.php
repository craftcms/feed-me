<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;
use verbb\feedme\events\FeedEvent;
use verbb\feedme\models\FeedModel;
use verbb\feedme\records\FeedRecord;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use craft\models\Section;

use Cake\Utility\Hash;

class Feeds extends Component
{
    // Constants
    // =========================================================================

    const EVENT_BEFORE_SAVE_FEED = 'onBeforeSaveFeed';
    const EVENT_AFTER_SAVE_FEED = 'onAfterSaveFeed';


    // Public Methods
    // =========================================================================

    public function getFeeds()
    {
        $results = $this->_getQuery()
            ->all();

        foreach ($results as $key => $result) {
            $results[$key] = $this->_createModelFromRecord($result);
        }

        return $results;
    }

    public function getTotalFeeds()
    {
        return count($this->getFeeds());
    }

    public function getFeedById($feedId)
    {
        $result = $this->_getQuery()
            ->where(['id' => $feedId])
            ->one();

        return $this->_createModelFromRecord($result);
    }

    public function saveFeed(FeedModel $model, bool $runValidation = true): bool
    {
        $isNewModel = !$model->id;

        // Fire a 'beforeSaveFeed' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_FEED)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_FEED, new FeedEvent([
                'feed' => $model,
                'isNew' => $isNewModel,
            ]));
        }

        if ($runValidation && !$model->validate()) {
            Craft::info('Feed not saved due to validation error.', __METHOD__);
            return false;
        }

        if ($isNewModel) {
            $record = new FeedRecord();
        } else {
            $record = FeedRecord::findOne($model->id);

            if (!$record) {
                throw new FeedException(Craft::t('feed-me', 'No feed exists with the ID “{id}”', ['id' => $model->id]));
            }
        }

        $record->name               = $model->name;
        $record->feedUrl            = $model->feedUrl;
        $record->feedType           = $model->feedType;
        $record->primaryElement     = $model->primaryElement;
        $record->elementType        = $model->elementType;
        $record->siteId             = $model->siteId;
        $record->duplicateHandle    = $model->duplicateHandle;
        $record->paginationNode     = $model->paginationNode;
        $record->passkey            = $model->passkey;
        $record->backup             = $model->backup;

        if ($model->elementGroup) {
            $record->setAttribute('elementGroup', json_encode($model->elementGroup));
        }

        if ($model->fieldMapping) {
            $record->setAttribute('fieldMapping', json_encode($model->fieldMapping));
        }

        if ($model->fieldUnique) {
            $record->setAttribute('fieldUnique', json_encode($model->fieldUnique));
        }

        $record->save(false);

        if (!$model->id) {
            $model->id = $record->id;
            $model->fieldMapping = $record->fieldMapping;
            $model->fieldUnique = $record->fieldUnique;
        }

        // Fire an 'afterSaveFeed' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_FEED)) {
            $this->trigger(self::EVENT_AFTER_SAVE_FEED, new FeedEvent([
                'feed' => $model,
                'isNew' => $isNewModel,
            ]));
        }

        return true;
    }

    public function deleteFeedById($feedId)
    {
        return Craft::$app->getDb()->createCommand()
            ->delete('{{%feedme_feeds}}', ['id' => $feedId])
            ->execute();
    }

    public function duplicateFeed($feed)
    {
        $feed->id = null;

        return $this->saveFeed($feed);
    }


    // Private Methods
    // =========================================================================

    private function _getQuery()
    {
        return FeedRecord::find()
            ->select([
                'id',
                'name',
                'feedUrl',
                'feedType',
                'primaryElement',
                'elementType',
                'elementGroup',
                'siteId',
                'duplicateHandle',
                'paginationNode',
                'fieldMapping',
                'fieldUnique',
                'passkey',
                'backup',
                'dateCreated',
                'dateUpdated',
                'uid',
            ]);
    }

    private function _createModelFromRecord(FeedRecord $record = null)
    {
        if (!$record) {
            return null;
        }

        $record['elementGroup'] = Json::decode($record['elementGroup']);
        $record['duplicateHandle'] = Json::decode($record['duplicateHandle']);
        $record['fieldMapping'] = Json::decode($record['fieldMapping']);
        $record['fieldUnique'] = Json::decode($record['fieldUnique']);

        return new FeedModel($record->toArray());
    }

}
