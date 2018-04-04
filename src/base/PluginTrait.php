<?php
namespace verbb\feedme\base;

use verbb\feedme\FeedMe;
use verbb\feedme\services\DataTypes;
use verbb\feedme\services\Elements;
use verbb\feedme\services\Feeds;
use verbb\feedme\services\Fields;
use verbb\feedme\services\Logs;
use verbb\feedme\services\Process;
use verbb\feedme\services\Service;

use Craft;

use Cake\Utility\Hash;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    /**
     * @var FeedMe
     */
    public static $plugin;


    // Static Methods
    // =========================================================================

    public static function error($feed = null, $message, array $params = [])
    {
        if (isset($feed['name'])) {
            $message = $feed['name'] . ': ' . $message;
        }

        $message = Craft::t('feed-me', $message, $params);

        FeedMe::$plugin->getLogs()->log($message, __METHOD__);
        Craft::error($message, __METHOD__);
    }

    public static function info($feed = null, $message, array $params = [])
    {
        if (isset($feed['name'])) {
            $message = $feed['name'] . ': ' . $message;
        }

        $message = Craft::t('feed-me', $message, $params);

        FeedMe::$plugin->getLogs()->log($message, __METHOD__);
        Craft::info($message, __METHOD__);
    }

    public static function debug($feed = null, $message)
    {
        if (isset($feed['debug'])) {
            echo "<pre>";
            print_r($message);
            echo "</pre>";
        }
    }


    // Public Methods
    // =========================================================================

    public function getData()
    {
        return $this->get('data');
    }

    public function getElements()
    {
        return $this->get('elements');
    }

    public function getFeeds()
    {
        return $this->get('feeds');
    }

    public function getFields()
    {
        return $this->get('fields');
    }

    public function getLogs()
    {
        return $this->get('logs');
    }

    public function getProcess()
    {
        return $this->get('process');
    }

    public function getService()
    {
        return $this->get('service');
    }

    private function _setPluginComponents()
    {
        $this->setComponents([
            'data'     => DataTypes::class,
            'elements' => Elements::class,
            'feeds'    => Feeds::class,
            'fields'   => Fields::class,
            'logs'     => Logs::class,
            'process'  => Process::class,
            'service'  => Service::class,
        ]);
    }

}