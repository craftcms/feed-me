# Configuration

Feed Me allows you to provide additional options through a configuration file. Create a file named `feedme.php` in your `craft/config` directory. As with any configuration option, this supports multi-environment options.

```php

<?php

return array(
    '*' => array(
        'curlOptions' => array(
            // Example
            // CURLOPT_PROXY => 'username:password',
        ),
        'checkExistingFieldData' => false,
        'skipUpdateFieldHandle' => 'skipFeedMeUpdate',
        'backupLimit' => 100,
    ),
);
```

### Configuration options

- `curlOptions` - an array of options for cURL when fetching your feed data.
- `checkExistingFieldData` - a boolean whether to compare against an existing element (if any) and each field's content. If your feed content and the existing field's content match exactly, Feed Me won't overwrite the existing content. This can be useful to enable for performance.
- `skipUpdateFieldHandle` - a custom field (preferably Lightswitch) to allow you to opt-out of updating elements. If switched on for an element, Feed Me will skip over updating it, but update other elements in the feed.
- `backupLimit` - when backup is switched on for a feed, you can set a limit for the number to keep. This will ensure your backups don't get out of control on your server. By default, this is set to 100.