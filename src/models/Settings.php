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
     * @var bool|string
     */
    public bool|string $logging = true;

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

    /**
     * @var bool
     * @since 5.9.0
     */
    public bool $assetDownloadGuzzle = false;

    /**
     * Whether the pagination URL should allow path/relative URL values
     * @var bool
     * @since 5.15.0
     */
    public bool $allowPathInPaginationUrl = false;

    /**
     * Hostnames that are allowed to resolve to private IP addresses, e.g. `'feeds.internal'` or `'*.ddev.site'`.
     * A leading `*.` wildcard matches any subdomain. Can only be set in `config/feed-me.php`.
     * @var string[]
     * @since 6.16.0
     */
    public array $allowedPrivateHosts = [];

    /**
     * @inheritdoc
     */
    public function fields(): array
    {
        $fields = parent::fields();
        // allowedPrivateHosts, clientOptions, requestOptions, feedOptions can't be set from the UI so no point in storing them in the project config
        unset($fields['allowedPrivateHosts'], $fields['clientOptions'], $fields['requestOptions'], $fields['feedOptions']);

        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function extraFields(): array
    {
        return [
            'allowedPrivateHosts',
            'clientOptions',
            'requestOptions',
            'feedOptions'
        ];
    }
}
