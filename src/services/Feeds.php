<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\feedme\events\FeedEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\records\FeedRecord;
use craft\helpers\Json;

class Feeds extends Component
{
    // Properties
    // =========================================================================

    private $_overrides = [];


    // Constants
    // =========================================================================

    const EVENT_BEFORE_SAVE_FEED = 'onBeforeSaveFeed';
    const EVENT_AFTER_SAVE_FEED = 'onAfterSaveFeed';


    // Public Methods
    // =========================================================================

    public function getFeeds($orderBy = null)
    {
        $query = $this->_getQuery();

        if ($orderBy) {
            $query->orderBy($orderBy);
        }

        $results = $query->all();

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

        $record->name = $model->name;
        $record->feedUrl = $model->feedUrl;
        $record->feedType = $model->feedType;
        $record->primaryElement = $model->primaryElement;
        $record->elementType = $model->elementType;
        $record->siteId = $model->siteId;
        $record->duplicateHandle = $model->duplicateHandle;
        $record->paginationNode = $model->paginationNode;
        $record->passkey = $model->passkey;
        $record->backup = $model->backup;

        if ($model->elementGroup) {
            $record->setAttribute('elementGroup', json_encode($model->elementGroup));
        }

        if ($model->fieldMapping) {
            $record->setAttribute('fieldMapping', json_encode($model->fieldMapping));
        }

        if ($model->fieldUnique) {
            $record->setAttribute('fieldUnique', json_encode($model->fieldUnique));
        }

        if ($isNewModel) {
            $maxSortOrder = (new Query())
                ->from(['{{%feedme_feeds}}'])
                ->max('[[sortOrder]]');

            $record->sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;
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

    public function getModelOverrides($handle, $feedId)
    {
        if (!$this->_overrides) {
            $this->_overrides = Hash::get(Craft::$app->getConfig()->getConfigFromFile('feed-me'), 'feedOptions.' . $feedId);
        }

        return $this->_overrides[$handle] ?? null;
    }

    public function reorderFeeds(array $feedIds): bool
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        try {
            foreach ($feedIds as $feedOrder => $feedId) {
                $feedRecord = $this->_getFeedRecordById($feedId);
                $feedRecord->sortOrder = $feedOrder + 1;
                $feedRecord->save();
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
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
                'sortOrder',
                'duplicateHandle',
                'paginationNode',
                'fieldMapping',
                'fieldUnique',
                'passkey',
                'backup',
                'dateCreated',
                'dateUpdated',
                'uid',
            ])
            ->orderBy(['sortOrder' => SORT_ASC]);
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

        $attributes = $record->toArray();

        foreach ($attributes as $attribute => $value) {
            $override = $this->getModelOverrides($attribute, $record['id']);

            if ($override) {
                $attributes[$attribute] = $override;
            }
        }

        return new FeedModel($attributes);
    }

    private function _getFeedRecordById(int $feedId = null): FeedRecord
    {
        if ($feedId !== null) {
            $feedRecord = FeedRecord::findOne(['id' => $feedId]);

            if (!$feedRecord) {
                throw new Exception(Craft::t('feed-me', 'No feed exists with the ID “{id}”.', ['id' => $feedId]));
            }
        } else {
            $feedRecord = new FeedRecord();
        }

        return $feedRecord;
    }

}
