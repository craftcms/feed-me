<?php

namespace craft\feedme\models;

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
    public $compareContent = true;
    public $skipUpdateFieldHandle = '';
    public $backupLimit = 100;
    public $dataDelimiter = '-|-';
    public $csvColumnDelimiter = ',';
    public $parseTwig = [];
    public $feedOptions = [];
    public $sleepTime = 0;
    public $logging = true;
    public $runGcBeforeFeed = false;
    public $queueTtr;
    public $queueMaxRetry;
    public $assetDownloadCurl = false;

}
