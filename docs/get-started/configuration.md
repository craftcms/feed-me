# Configuration

Create a file named `feed-me.php` in your `config` directory.

```php
return [
    '*' => [
        // Support any options via http://docs.guzzlephp.org/en/stable/request-options.html
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
        'dataDelimeter' => '|',
    ],
];
```
