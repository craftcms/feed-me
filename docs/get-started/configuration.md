# Configuration

Create an `feed-me.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

```php
<?php

return [
    '*' => [
        'pluginName' => 'Feed Me',
        'cache' => 60,
        'requestOptions' => [
            'headers' => [
                'Accept' => 'application/json',
                'X-Foo' => ['Bar', 'Baz']
            ],
            'auth' => [
                'username',
                'password'
            ],
            'query' => [
                'foo' => 'bar'
            ],
        ],
        'checkExistingFieldData' => false,
        'skipUpdateFieldHandle' => 'skipFeedMeUpdate',
        'backupLimit' => 100,
        'dataDelimiter' => '-|-',
        'csvColumnDelimiter' => '',
        'parseTwig' => false,
    ]
];
```

### Configuration options

- `pluginName` - Optionally change the name of the plugin.
- `cache` - For template calls, change the default cache time.
- `requestOptions` - Any additionl options to be sent with requests when fetching your feed content [Guzzle Options](http://docs.guzzlephp.org/en/stable/request-options.html).
- `checkExistingFieldData` - Whether to do performance checks against existing data when updating. Can help to improve processing speed. 
- `skipUpdateFieldHandle` - A provided field handle attached to your elements (often a Lightswitch or similar). If this field has a value during processing, Feed Me will skip the element.
- `backupLimit` - Set a limit to the number of backups to keep.
- `dataDelimiter` - Feed Me will try and split content based on this delimiter. Useful for CSVs.
- `csvColumnDelimiter` - Optionally set the delimiter for columns in CSVs before fetching the content.
- `parseTwig` - Whether to parse field data and default values for Twig. Disabled by default.

## Control Panel

You can also make change and configuration settings through the Control Panel by visiting Settings → Feed Me.
