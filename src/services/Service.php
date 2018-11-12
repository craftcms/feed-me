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

    public function getConfig($key)
    {
        $settings = FeedMe::$plugin->getSettings();

        return Hash::get($settings, $key);
    }

    public function createGuzzleClient()
    {
        $options = $this->getConfig('clientOptions');

        return Craft::createGuzzleClient($options);
    }

    public function getRequestOptions()
    {
        $options = $this->getConfig('requestOptions');

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
