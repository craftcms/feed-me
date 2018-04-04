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
        $config = Craft::$app->getConfig()->getConfigFromFile('feed-me');

        return Hash::get($config, $key);
    }

    public function createGuzzleClient()
    {
        $configOptions = $this->getConfig('clientOptions');

        $defaultOptions = [];

        $options = Hash::merge($defaultOptions, $configOptions);

        return Craft::createGuzzleClient($options);
    }

    public function getRequestOptions()
    {
        $configOptions = $this->getConfig('requestOptions');

        $defaultOptions = [
            'headers' => [
                'User-Agent' => FeedMe::$plugin->getPluginName(),
            ]
        ];

        $options = Hash::merge($defaultOptions, $configOptions);

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
