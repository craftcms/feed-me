<?php

namespace craft\feedme\models;

use craft\base\Model;

class Settings extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public string $pluginName = 'Feed Me';

    /**
     * @var int
     */
    public int $cache = 60;

    /**
     * @var string|int|array
     */
    public string|int|array $enabledTabs = '*';

    /**
     * @var array
     */
    public array $clientOptions = [];

    /**
     * @var string[][]
     */
    public array $requestOptions = [
        'headers' => [
            'User-Agent' => 'Feed Me',
        ],
    ];

    /**
     * @var bool
     */
    public bool $compareContent = true;

    /**
     * @var string
     */
    public string $skipUpdateFieldHandle = '';

    /**
     * @var int
     */
    public int $backupLimit = 100;

    /**
     * @var string
     */
    public string $dataDelimiter = '-|-';

    /**
     * @var string
     */
    public string $csvColumnDelimiter = ',';

    /**
     * @var bool
     */
    public bool $parseTwig = false;

    /**
     * @var array
     */
    public array $feedOptions = [];

    /**
     * @var int
     */
    public int $sleepTime = 0;

    /**
     * @var bool
     */
    public bool $logging = true;

    /**
     * @var bool
     */
    public bool $runGcBeforeFeed = false;

    /**
     * @var int|null
     */
    public ?int $queueTtr = null;

    /**
     * @var int|null
     */
    public ?int $queueMaxRetry = null;

    /**
     * @var bool
     */
    public bool $assetDownloadCurl = false;
}
