<?php
namespace verbb\feedme\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $pluginName = 'Feed Me';

    public $cache = 60;

    public $enabledTabs = '*';
}