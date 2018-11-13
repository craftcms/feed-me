<?php
namespace verbb\feedme\services;

use verbb\feedme\FeedMe;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\models\Section;
use craft\events\RegisterComponentTypesEvent;

use Cake\Utility\Hash;

class Service extends Component
{
    // Public Methods
    // =========================================================================

    public function getConfig($key, $feedId = null)
    {
        $settings = FeedMe::$plugin->getSettings();

        // Get the config item from the global settings
        $configItem = Hash::get($settings, $key);

        // Or, check if there's a setting set per-feed
        if ($feedId) {
            $configFeedItem = Hash::get($settings, 'feedOptions.' . $feedId . '.' . $key);

            if ($configFeedItem) {
                $configItem = $configFeedItem;
            }
        }

        return $configItem;
    }

    public function createGuzzleClient($feedId = null)
    {
        $options = $this->getConfig('clientOptions', $feedId);

        return Craft::createGuzzleClient($options);
    }

    public function getRequestOptions($feedId = null)
    {
        $options = $this->getConfig('requestOptions', $feedId);

        return $options;
    }

    public function formatDateTime($dateTime)
    {
        return DateTimeHelper::toDateTime($dateTime);
    }

    public function isProEdition()
    {
        return (bool)Craft::$app->plugins->getPlugin('feed-me-pro');
    }

}
