<?php

namespace craft\feedme\base;

use Craft;
use craft\feedme\Plugin;
use craft\feedme\services\DataTypes;
use craft\feedme\services\Elements;
use craft\feedme\services\Feeds;
use craft\feedme\services\Fields;
use craft\feedme\services\Logs;
use craft\feedme\services\Process;
use craft\feedme\services\Service;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    /**
     * @var Plugin
     */
    public static $plugin;

    /**
     * @var string $feedName Keeping state for logging
     */
    public static $feedName;

    /**
     * @var
     */
    public static $stepKey;


    // Static Methods
    // =========================================================================

    /**
     * @param $message
     * @param array $params
     * @param array $options
     */
    public static function error($message, $params = [], $options = [])
    {
        Plugin::$plugin->getLogs()->log(__METHOD__, $message, $params, $options);
    }

    /**
     * @param $message
     * @param array $params
     * @param array $options
     */
    public static function info($message, $params = [], $options = [])
    {
        Plugin::$plugin->getLogs()->log(__METHOD__, $message, $params, $options);
    }

    /**
     * @param $message
     */
    public static function debug($message)
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        if (Craft::$app->getRequest()->getSegment(-1) === 'debug') {
            echo "<pre>";
            print_r($message);
            echo "</pre>";
        }
    }


    // Public Methods
    // =========================================================================

    /**
     * @return DataTypes
     */
    public function getData()
    {
        return $this->get('data');
    }

    /**
     * @return Elements
     */
    public function getElements()
    {
        return $this->get('elements');
    }

    /**
     * @return Feeds
     */
    public function getFeeds()
    {
        return $this->get('feeds');
    }

    /**
     * @return Fields
     */
    public function getFields()
    {
        return $this->get('fields');
    }

    /**
     * @return Logs
     */
    public function getLogs()
    {
        return $this->get('logs');
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->get('process');
    }

    /**
     * @return Service
     */
    public function getService()
    {
        return $this->get('service');
    }


    // Private Methods
    // =========================================================================

    /**
     *
     */
    private function _setPluginComponents()
    {
        $this->setComponents([
            'data' => DataTypes::class,
            'elements' => Elements::class,
            'feeds' => Feeds::class,
            'fields' => Fields::class,
            'logs' => Logs::class,
            'process' => Process::class,
            'service' => Service::class,
        ]);
    }
}
