<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\db\ActiveQuery;
use craft\db\Query;
use craft\feedme\errors\FeedException;
use craft\feedme\events\FeedEvent;
use craft\feedme\models\FeedModel;
use craft\feedme\records\FeedRecord;
use craft\helpers\Json;
use Exception;
use Throwable;

/**
 *
 * @property-read mixed $totalFeeds
 */
class Feeds extends Component
{
    // Properties
    // =========================================================================

    /**
     * @var array
     */
    private array $_overrides = [];

    // Constants
    // =========================================================================

    public const EVENT_BEFORE_SAVE_FEED = 'onBeforeSaveFeed';
    public const EVENT_AFTER_SAVE_FEED = 'onAfterSaveFeed';


    // Public Methods
    // =========================================================================

    /**
     * @param null $orderBy
     * @return array
     */
    public function getFeeds($orderBy = null): array
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

    /**
     * @return int
     */
    public function getTotalFeeds(): int
    {
        return count($this->getFeeds());
    }

    /**
     * @param $feedId
     * @return FeedModel|null
     */
    public function getFeedById($feedId): ?FeedModel
    {
        $result = $this->_getQuery()
            ->where(['id' => $feedId])
            ->one();

        return $this->_createModelFromRecord($result);
    }

    /**
     * @param FeedModel $model
     * @param bool $runValidation
     * @return bool
     * @throws Exception
     */
    public function saveFeed(FeedModel $model, bool $runValidation = true): bool
    {
        // If a singleton group was selected, then override the import strategy selection
        if ($model->singleton) {
            $model->duplicateHandle = ['update'];
        }

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
                throw new Exception(Craft::t('feed-me', 'No feed exists with the ID “{id}”', ['id' => $model->id]));
            }
        }

        $record->name = $model->name;
        $record->feedUrl = $model->feedUrl;
        $record->feedType = $model->feedType;
        $record->primaryElement = $model->primaryElement;
        $record->elementType = $model->elementType;
        $record->siteId = $model->siteId;
        $record->singleton = $model->singleton;
        $record->duplicateHandle = $model->duplicateHandle;
        $record->updateSearchIndexes = $model->updateSearchIndexes;
        $record->paginationNode = $model->paginationNode;
        $record->passkey = $model->passkey;
        $record->backup = $model->backup;

        if ($model->elementGroup) {
            $record->setAttribute('elementGroup', Json::encode($model->elementGroup));
        }

        if ($model->fieldMapping) {
            $record->setAttribute('fieldMapping', Json::encode($model->fieldMapping));
        }

        if ($model->fieldUnique) {
            $record->setAttribute('fieldUnique', Json::encode($model->fieldUnique));
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

    /**
     * @param $feedId
     * @return int
     * @throws \yii\db\Exception
     */
    public function deleteFeedById($feedId): int
    {
        return Craft::$app->getDb()->createCommand()
            ->delete('{{%feedme_feeds}}', ['id' => $feedId])
            ->execute();
    }

    /**
     * @param $feed
     * @return bool
     * @throws Exception
     */
    public function duplicateFeed($feed): bool
    {
        $feed->id = null;

        return $this->saveFeed($feed);
    }

    /**
     * @param $handle
     * @param $feedId
     * @return mixed|null
     */
    public function getModelOverrides($handle, $feedId): mixed
    {
        if (empty($this->_overrides[$feedId])) {
            $this->_overrides[$feedId] = Hash::get(Craft::$app->getConfig()->getConfigFromFile('feed-me'), 'feedOptions.' . $feedId);
        }

        return $this->_overrides[$feedId][$handle] ?? null;
    }

    /**
     * @param array $feedIds
     * @return bool
     * @throws Throwable
     * @throws \yii\db\Exception
     */
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
        } catch (Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }


    // Private Methods
    // =========================================================================

    /**
     * @return ActiveQuery
     */
    private function _getQuery(): ActiveQuery
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
                'singleton',
                'duplicateHandle',
                'updateSearchIndexes',
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

    /**
     * @param FeedRecord|null $record
     * @return FeedModel|null
     */
    private function _createModelFromRecord(FeedRecord $record = null): ?FeedModel
    {
        if (!$record) {
            return null;
        }

        $attributes = $record->toArray();

        $attributes['elementGroup'] = Json::decode($attributes['elementGroup']);
        $attributes['duplicateHandle'] = Json::decode($attributes['duplicateHandle']);
        $attributes['fieldMapping'] = Json::decode($attributes['fieldMapping']);
        $attributes['fieldUnique'] = Json::decode($attributes['fieldUnique']);

        foreach ($attributes as $attribute => $value) {
            $override = $this->getModelOverrides($attribute, $record['id']);

            if ($override) {
                $attributes[$attribute] = $override;
            }
        }

        return new FeedModel($attributes);
    }

    /**
     * @param int|null $feedId
     * @return FeedRecord
     * @throws Exception
     */
    private function _getFeedRecordById(int $feedId = null): FeedRecord
    {
        if ($feedId !== null) {
            $feedRecord = FeedRecord::findOne(['id' => $feedId]);

            if (!$feedRecord) {
                throw new FeedException(Craft::t('feed-me', 'No feed exists with the ID “{id}”.', ['id' => $feedId]));
            }
        } else {
            $feedRecord = new FeedRecord();
        }

        return $feedRecord;
    }
}
