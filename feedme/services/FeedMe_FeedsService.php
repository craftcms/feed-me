<?php
namespace Craft;

class FeedMe_FeedsService extends BaseApplicationComponent
{
    public function getFeeds()
    {
        return FeedMe_FeedRecord::model()->findAll();
    }

	public function getTotalFeeds()
	{
		return count($this->getFeeds());
	}

	public function getFeedById($feedId)
    {
        $feedRecord = FeedMe_FeedRecord::model()->findById($feedId);
        if ($feedRecord) {
            return FeedMe_FeedModel::populateModel($feedRecord);
        }
        return null;
    }

    public function getFeedForTemplate($options = array())
    {
        $plugin = craft()->plugins->getPlugin('feedMe');
        $settings = $plugin->getSettings();

        $url = (array_key_exists('url', $options) ? $options['url'] : null);
        $element = (array_key_exists('element', $options) ? $options['element'] : '');
        $cache = (array_key_exists('cache', $options) ? $options['cache'] : true);
        $cacheId = $url . '#' . $element; // cache for this URL and Element Node

        // URL = required
        if (!$url) {
            return array();
        }

        // If cache explicitly set to false, always return latest data
        if ($cache === false) {
            return craft()->feedMe_feedXML->getFeed($url, $element);
        }

        // We want some caching action!
        if (is_numeric($cache) || $cache === true) {
            $cache = (is_numeric($cache)) ? $cache : $settings->cache;

            $cachedRequest = craft()->feedMe_cache->get($cacheId);

            if ($cachedRequest) {
                return $cachedRequest;
            } else {
                $data = craft()->feedMe_feedXML->getFeed($url, $element);
                craft()->feedMe_cache->set($cacheId, $data, $cache);

                return $data;
            }
        }
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
        $feedRecord->setAttributes($feed->getAttributes());

        if ($feed->fieldMapping) {
            $feedRecord->setAttribute('fieldMapping', json_encode($feed->fieldMapping));
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
            	$feed->setAttribute('id', $feedRecord->getAttribute('id'));
                $feed->setAttribute('fieldMapping', $feedRecord->getAttribute('fieldMapping'));
                $feed->setAttribute('fieldUnique', $feedRecord->getAttribute('fieldUnique'));

            	return true;
            } else {
            	$feed->addErrors($feedRecord->getErrors());
            	return false;
            }
        } else {
            //die(print_r($feed->getErrors()));
        }

        return false;
	}

    public function deleteFeedById($feedId)
    {
        return craft()->db->createCommand()->delete('feedme_feeds', array('id' => $feedId));
    }
}
