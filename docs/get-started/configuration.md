# Configuration

Create an `feed-me.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

```php
<?php

return [
    '*' => [
        'pluginName' => 'Feed Me',
        'cache' => 60,
        'requestOptions' => [],
        'skipUpdateFieldHandle' => 'skipFeedMeUpdate',
        'backupLimit' => 100,
        'dataDelimiter' => '-|-',
        'csvColumnDelimiter' => '',
        'parseTwig' => false,
        'compareContent' => true,
        'sleepTime' => 0,
        'logging' => true,
        'runGcBeforeFeed' => false,
        'queueTtr' => 300,
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

- `pluginName` - Optionally change the name of the plugin.
- `cache` - For template calls, change the default cache time.
- `requestOptions` - Any additionl options to be sent with requests when fetching your feed content [Guzzle Options](http://docs.guzzlephp.org/en/stable/request-options.html). See an example below.
- `skipUpdateFieldHandle` - A provided field handle attached to your elements (often a Lightswitch or similar). If this field has a value during processing, Feed Me will skip the element.
- `backupLimit` - Set a limit to the number of backups to keep.
- `dataDelimiter` - Feed Me will try and split content based on this delimiter. Useful for CSVs.
- `csvColumnDelimiter` - Optionally set the delimiter for columns in CSVs before fetching the content.
- `parseTwig` - Whether to parse field data and default values for Twig. Disabled by default.
- `compareContent` - Whether to check against existing element content before updating. This can have considerable performance improvements and prevent against needless updating.
- `sleepTime` - Add the number of seconds to sleep after each feed item has been processed.
- `logging` - Set the level of logging to do. Possible values are `true` (default) to log everything, `false` to disable logging or `error` to only record errors.
- `runGcBeforeFeed` - Whether to run the Garbage Collection service before running a feed.
- `queueTtr` - Set the 'time to reserve' time in seconds, to prevent the job being cancelled after 300 seconds (default).
- `queueMaxRetry` - Set the maxiumum amount of retries the queue job should have before failing.
- `assetDownloadCurl` - Use curl to download assets from a remote source. Can be used when issues arise using the default implementation.
- `feedOptions` - Provide an array of any of the above options or [Feed Settings](../feature-tour/feed-overview.md) to set specifically for certain feeds. Use the Feed ID as the key for the array.

#### Example `requestOptions`
See [Guzzle Options](http://docs.guzzlephp.org/en/stable/request-options.html) for the full range:

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

You can also manage configuration settings through the Control Panel by visiting Settings → Feed Me.
