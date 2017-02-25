<?php
namespace Craft;

class FeedMe_FeedsService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function getFeeds()
    {
        $feedRecords = FeedMe_FeedRecord::model()->findAll();
        return FeedMe_FeedModel::populateModels($feedRecords);
    }

    public function getTotalFeeds()
    {
        return count($this->getFeeds());
    }

    public function getFeedById($feedId)
    {
        $feedRecord = FeedMe_FeedRecord::model()->findById($feedId);
        return FeedMe_FeedModel::populateModel($feedRecord);
    }

    public function saveFeed(FeedMe_FeedModel $feed)
    {
        if ($feed->id) {
            $feedRecord = FeedMe_FeedRecord::model()->findById($feed->id);

            if (!$feedRecord) {
                throw new Exception(Craft::t('No feed exists with the ID “{id}”', array('id' => $feed->id)));
            }
        } else {
            $feedRecord = new FeedMe_FeedRecord();
        }

        // Set attributes
        $feedRecord->name               = $feed->name;
        $feedRecord->feedUrl            = $feed->feedUrl;
        $feedRecord->feedType           = $feed->feedType;
        $feedRecord->primaryElement     = $feed->primaryElement;
        $feedRecord->elementType        = $feed->elementType;
        $feedRecord->locale             = $feed->locale;
        $feedRecord->duplicateHandle    = $feed->duplicateHandle;
        $feedRecord->passkey            = $feed->passkey;
        $feedRecord->backup             = $feed->backup;

        if ($feed->elementGroup) {
            $feedRecord->setAttribute('elementGroup', json_encode($feed->elementGroup));
        }

        if ($feed->fieldMapping) {
            $feedRecord->setAttribute('fieldMapping', json_encode($feed->fieldMapping));
        }

        if ($feed->fieldDefaults) {
            $feedRecord->setAttribute('fieldDefaults', json_encode($feed->fieldDefaults));
        }

        if ($feed->fieldElementMapping) {
            $feedRecord->setAttribute('fieldElementMapping', json_encode($feed->fieldElementMapping));
        }

        if ($feed->fieldElementDefaults) {
            $feedRecord->setAttribute('fieldElementDefaults', json_encode($feed->fieldElementDefaults));
        }

        if ($feed->fieldUnique) {
            $feedRecord->setAttribute('fieldUnique', json_encode($feed->fieldUnique));
        }

        // Validate
        $feedRecord->validate();
        $feed->addErrors($feedRecord->getErrors());

        // Save feed
        if (!$feed->hasErrors()) {
            if ($feedRecord->save()) {

                // Update Model with ID from Database
                $feed->id = $feedRecord->id;
                $feed->fieldMapping = $feedRecord->fieldMapping;
                $feed->fieldDefaults = $feedRecord->fieldDefaults;
                $feed->fieldElementMapping = $feedRecord->fieldElementMapping;
                $feed->fieldElementDefaults = $feedRecord->fieldElementDefaults;
                $feed->fieldUnique = $feedRecord->fieldUnique;

                return true;
            } else {
                $feed->addErrors($feedRecord->getErrors());
                return false;
            }
        }

        return false;
    }

    public function deleteFeedById($feedId)
    {
        return craft()->db->createCommand()->delete('feedme_feeds', array('id' => $feedId));
    }

}
