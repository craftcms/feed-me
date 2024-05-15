# Events

Events can be used to extend the functionality of Feed Me.

## Feeds related events

### The `beforeSaveFeed` event

Plugins can get notified before a feed has been saved (through the control panel).

```php
use craft\feedme\events\FeedEvent;
use craft\feedme\services\Feeds;
use yii\base\Event;

Event::on(Feeds::class, Feeds::EVENT_BEFORE_SAVE_FEED, function(FeedEvent $event) {

});
```

### The `afterSaveFeed` event

Plugins can get notified after a feed has been saved (through the control panel).

```php
use craft\feedme\events\FeedEvent;
use craft\feedme\services\Feeds;
use yii\base\Event;

Event::on(Feeds::class, Feeds::EVENT_AFTER_SAVE_FEED, function(FeedEvent $event) {

});
```


## Data Fetching related events

### The `beforeFetchFeed` event

Plugins can get notified before a feed's data has been fetched. You can also return with a response to bypass Feed Me's default fetching.

```php
use craft\feedme\events\FeedDataEvent;
use craft\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, function(FeedDataEvent $event) {
    // This will set the feed's data
    $event->response = [
        'success' => true,
        'data' => '<?xml version="1.0" encoding="UTF-8"?><entries><entry><title>Some Title</title></entry></entries>',
    ];
});
```

### The `afterFetchFeed` event

Plugins can get notified after a feed's data has been fetched. Note the feed data hasn't been parsed at this point.

```php
use craft\feedme\events\FeedDataEvent;
use craft\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_AFTER_FETCH_FEED, function(FeedDataEvent $event) {

});
```

### The `afterParseFeed` event

Plugins can get notified after a feed's data has been fetched and parsed into an array.

```php
use craft\feedme\events\FeedDataEvent;
use craft\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_AFTER_PARSE_FEED, function(FeedDataEvent $event) {

});
```


## Feed Processing related events

### The `beforeProcessFeed` event

Plugins can get notified before the feed processing has started.

```php
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_BEFORE_PROCESS_FEED, function(FeedProcessEvent $event) {

});
```

### The `afterProcessFeed` event

Plugins can get notified after the feed processing has completed (all items are done).

```php
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_AFTER_PROCESS_FEED, function(FeedProcessEvent $event) {

});
```

### The `stepBeforeElementMatch` event

Triggered for each feed item, plugins can get notified before existing elements are tried to be matched.

```php
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_MATCH, function(FeedProcessEvent $event) {

});
```

### The `stepBeforeElementSave` event

Triggered for each feed item, plugins can get notified before the prepared element is about to be saved.

```php
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {

});
```

### The `stepAfterElementSave` event

Triggered for each feed item, plugins can get notified after the prepared element has been saved.

```php
use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_AFTER_ELEMENT_SAVE, function(FeedProcessEvent $event) {

});
```

## Field parsing related events

### The `beforeParseField` event

Triggered before a field value is parsed. Plugins can get notified before a field value is parsed.

```php
use craft\feedme\events\FieldEvent;
use craft\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_BEFORE_PARSE_FIELD, function(FieldEvent $event) {

});
```

### The `afterParseField` event

Triggered after a field value is parsed. Plugins can get notified before a field value is parsed and alter the parsed value. 

```php
use craft\feedme\events\FieldEvent;
use craft\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_AFTER_PARSE_FIELD, function(FieldEvent $event) {

});
```