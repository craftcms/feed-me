<?php

namespace craft\feedme\services;

use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\feedme\Plugin;

class Service extends Component
{
    // Public Methods
    // =========================================================================

    public function getConfig($key, $feedId = null)
    {
        $settings = Plugin::$plugin->getSettings();

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
        return $this->getConfig('requestOptions', $feedId);
    }

    public function formatDateTime($dateTime)
    {
        return DateTimeHelper::toDateTime($dateTime);
    }

}
