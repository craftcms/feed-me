<?php

namespace verbb\feedme\base;

use Craft;
use verbb\feedme\Plugin;
use verbb\feedme\services\DataTypes;
use verbb\feedme\services\Elements;
use verbb\feedme\services\Feeds;
use verbb\feedme\services\Fields;
use verbb\feedme\services\Logs;
use verbb\feedme\services\Process;
use verbb\feedme\services\Service;

trait PluginTrait
{
    // Static Properties
    // =========================================================================

    public static $plugin;

    // Keeping state for logging
    public static $feedName;
    public static $stepKey;


    // Static Methods
    // =========================================================================

    public static function error($message, $params = [], $options = [])
    {
        Plugin::$plugin->getLogs()->log(__METHOD__, $message, $params, $options);
    }

    public static function info($message, $params = [], $options = [])
    {
        Plugin::$plugin->getLogs()->log(__METHOD__, $message, $params, $options);
    }

    public static function debug($message, $params = [])
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


    // Private Methods
    // =========================================================================

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
