# Events Reference

To learn more about how events work, see the [Craft documentation](http://buildwithcraft.com/docs/plugins/hooks-and-events#events) on events.

### onBeforeFetchFeed

Raised before the feed is fetched from the URL provided. You also have the chance to modify the URL.

#### Parameters

-  `url` – The URL that this feed is pointing to. Note you can modify this if required.
-  `element` – The element this feed is importing into.
-  `settings` – The FeedModel used to process this feed.

```php
craft()->on('feedMe_process.onBeforeFetchFeed', function($event) {
    $url = $event->params['url'];
    $element = $event->params['element'];
    $settings = $event->params['settings'];

    // Override the URL
    $event->params['url'] = 'https://someotherurl.com/test.xml';
});
```

### onFetchFeed

Raised after the feed has been fetched from the URL.

#### Parameters

- `data` – The data returned from the URL provided. This will be formatted as an array.

```php
craft()->on('feedMe_process.onBeforeFetchFeed', function($event) {
    $data = $event->params['data'];
});
```

### onBeforeProcessFeed

Raised before the feed is processed.

#### Parameters

- `settings` – The FeedModel used to process this feed.

```php
craft()->on('feedMe_process.onBeforeProcessFeed', function($event) {
    $settings = $event->params['settings'];
});
```

### onStepProcessFeed

Raised after each element has been processed by the feed - either successfully, or failed.

#### Parameters

- `settings` – The FeedModel used to process this feed.

```php
craft()->on('feedMe_process.onStepProcessFeed', function($event) {
    $settings = $event->params['settings'];
});
```

### onProcessFeed

Raised after the feed has finished processing all items.

#### Parameters

- `settings` – The FeedModel used to process this feed.

```php
craft()->on('feedMe_process.onProcessFeed', function($event) {
    $settings = $event->params['settings'];
});
```