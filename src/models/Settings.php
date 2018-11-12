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
    public $clientOptions = [];
    public $requestOptions = [
        'headers' => [
            'User-Agent' => 'Feed Me',
        ]
    ];
    public $checkContentBeforeUpdating = false;
    public $skipUpdateFieldHandle = '';
    public $backupLimit = 100;
    public $dataDelimiter = '-|-';
    public $csvColumnDelimiter = ',';
    public $parseTwig = [];

}