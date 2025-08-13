# Configuration

Create a `feed-me.php` file under your `/config` directory with any of the following. Feed Me’s configuration file allows customization [per-environment](https://craftcms.com/docs/4.x/config/#multi-environment-configs) and [per-feed](#configuration-options) (see `feedOptions`).

```php
<?php

return [
    '*' => [
        'pluginName' => 'Feed Me',
        'cache' => 60,
        'enabledTabs' => '*',
        'clientOptions' => [],
        'requestOptions' => [],
        'compareContent' => true,
        'skipUpdateFieldHandle' => 'skipFeedMeUpdate',
        'backupLimit' => 100,
        'dataDelimiter' => '-|-',
        'csvColumnDelimiter' => ',',
        'parseTwig' => false,
        'sleepTime' => 0,
        'logging' => true,
        'runGcBeforeFeed' => false,
        'queueTtr' => null,
        'queueMaxRetry' => 5,
        'assetDownloadCurl' => false,
        'feedOptions' => [
            '1' => [
                'feedUrl' => 'https://specialurl.io/feed.json',
                'requestOptions' => [],
            ]
        ],
    ]
];
```

### Configuration options

- `pluginName` — Optionally change the name of the plugin.
- `cache` — For template calls, change the default cache time.
- `enabledTabs` — Hide tabs in the Feed Me navigation. Pass an array with one or more of: `feeds`, `logs`, and `settings`. _This does not affect permissions, only the tabs’ visibility!_
- `clientOptions` — An array of Guzzle [client options](https://docs.guzzlephp.org/en/stable/quickstart.html#creating-a-client) to be merged with Craft’s defaults.
- `requestOptions` — Any additional options to be sent with requests when fetching your feed content Guzzle [request options](https://docs.guzzlephp.org/en/stable/request-options.html). See an example below.
- `compareContent` — Whether to check against existing element content before updating. This can have considerable performance improvements and prevent against needless updating.
- `skipUpdateFieldHandle` — Feed Me checks the provided field handle on each element that _would_ be updated while processing a feed. If its value is exactly `'1'`, the element is skipped. _This behavior only supports legacy Lightswitch fields in Craft 3.x._ If you need to control which elements in a feed are updated, consider preventing it via the `craft\feedme\services\Process::EVENT_STEP_BEFORE_PARSE_CONTENT` event.
- `backupLimit` — Set a limit to the number of backups to keep.
- `dataDelimiter` — Feed Me will try and split field values based on this delimiter. Useful for [table](https://craftcms.com/docs/5.x/reference/field-types/table.html), [checkboxes](https://craftcms.com/docs/5.x/reference/field-types/checkboxes.html), and [multiselect](https://craftcms.com/docs/5.x/reference/field-types/multi-select.html) fields.
- `csvColumnDelimiter` — Optionally set the delimiter for columns in CSVs before fetching the content.
- `parseTwig` — Whether to parse field data and default values for Twig. Disabled by default.
- `sleepTime` — Add the number of seconds to sleep after each feed item has been processed.
- `logging` — Set the level of logging to do. Possible values are `true` (default) to log everything, `false` to disable logging or `error` to only record errors.
- `runGcBeforeFeed` — Whether to run the Garbage Collection service before running a feed.
- `queueTtr` — Set the 'time to reserve' time in seconds, to prevent the job being cancelled after 300 seconds (default).
- `queueMaxRetry` — Set the maximum amount of retries the queue job should have before failing.
- `assetDownloadCurl` — Use curl to download assets from a remote source. Can be used when issues arise using the default implementation.
- `assetDownloadGuzzle` — Use Guzzle to download assets from a remote source. Can be used when issues arise using the default implementation.
- `feedOptions` — Provide an array of any of the above options (or [feed settings](../feature-tour/feed-overview.md)) to set or override for specific feeds, keyed by their existing feed IDs. _Note that feed IDs may be different across environments!_

#### Example `requestOptions`

See [Guzzle Options](https://docs.guzzlephp.org/en/stable/request-options.html) for the full range:

```php
'requestOptions' => [
    'headers' => [
        'Accept' => 'application/json',
    ],
    'auth' => [
        'username', 'password'
    ],
    'query' => [
        'foo' => 'bar'
    ],
],
```

## Control Panel

You can also manage configuration settings through the control panel by visiting Settings → Feed Me.
