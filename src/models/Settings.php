<?php

namespace craft\feedme\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $pluginName = 'Feed Me';

    /**
     * @var int
     */
    public $cache = 60;

    /**
     * @var string
     */
    public $enabledTabs = '*';

    /**
     * @var array
     */
    public $clientOptions = [];

    /**
     * @var \string[][]
     */
    public $requestOptions = [
        'headers' => [
            'User-Agent' => 'Feed Me',
        ],
    ];

    /**
     * @var bool
     */
    public $compareContent = true;

    /**
     * @var string
     */
    public $skipUpdateFieldHandle = '';

    /**
     * @var int
     */
    public $backupLimit = 100;

    /**
     * @var string
     */
    public $dataDelimiter = '-|-';

    /**
     * @var string
     */
    public $csvColumnDelimiter = ',';

    /**
     * @var array
     */
    public $parseTwig = [];

    /**
     * @var array
     */
    public $feedOptions = [];

    /**
     * @var int
     */
    public $sleepTime = 0;

    /**
     * @var bool
     */
    public $logging = true;

    /**
     * @var bool
     */
    public $runGcBeforeFeed = false;

    /**
     * @var
     */
    public $queueTtr;

    /**
     * @var
     */
    public $queueMaxRetry;

    /**
     * @var bool
     */
    public $assetDownloadCurl = false;
}
