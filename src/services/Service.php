<?php

namespace craft\feedme\services;

use ArrayAccess;
use Cake\Utility\Hash;
use Craft;
use craft\base\Component;
use craft\feedme\Plugin;
use craft\helpers\DateTimeHelper;
use DateTime;
use GuzzleHttp\Client;

class Service extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * @param $key
     * @param null $feedId
     * @return array|ArrayAccess|mixed|null
     */
    public function getConfig($key, $feedId = null): mixed
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

    /**
     * @param null $feedId
     * @return Client
     */
    public function createGuzzleClient($feedId = null): Client
    {
        $options = $this->getConfig('clientOptions', $feedId);

        return Craft::createGuzzleClient($options);
    }

    /**
     * @param null $feedId
     * @return array|ArrayAccess|mixed|null
     */
    public function getRequestOptions($feedId = null): mixed
    {
        return $this->getConfig('requestOptions', $feedId);
    }

    /**
     * @param $dateTime
     * @return DateTime|false
     * @throws \Exception
     */
    public function formatDateTime($dateTime): DateTime|bool
    {
        return DateTimeHelper::toDateTime($dateTime);
    }
}
