# Events

Events can be used to extend the functionality of Feed Me.

## Feeds related events

### The `beforeSaveFeed` event

Plugins can get notified before a feed has been saved (through the control panel).

```php
use verbb\feedme\events\FeedEvent;
use verbb\feedme\services\Feeds;
use yii\base\Event;

Event::on(Feeds::class, Feeds::EVENT_BEFORE_SAVE_FEED, function(FeedEvent $event) {

});
```

### The `beforeSaveFeed` event

Plugins can get notified after a feed has been saved (through the control panel).

```php
use verbb\feedme\events\FeedEvent;
use verbb\feedme\services\Feeds;
use yii\base\Event;

Event::on(Feeds::class, Feeds::EVENT_AFTER_SAVE_FEED, function(FeedEvent $event) {

});
```


## Data Fetching related events

### The `beforeFetchFeed` event

Plugins can get notified before a feed's data has been fetched.

```php
use verbb\feedme\events\FeedDataEvent;
use verbb\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_BEFORE_FETCH_FEED, function(FeedDataEvent $event) {

});
```

### The `afterFetchFeed` event

Plugins can get notified after a feed's data has been fetched.

```php
use verbb\feedme\events\FeedDataEvent;
use verbb\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_AFTER_FETCH_FEED, function(FeedDataEvent $event) {

});
```


## Feed Processing related events

### The `beforeProcessFeed` event

Plugins can get notified before the feed processing has started.

```php
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_BEFORE_PROCESS_FEED, function(FeedProcessEvent $event) {

});
```

### The `afterProcessFeed` event

Plugins can get notified after the feed processing has completed (all items are done).

```php
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_AFTER_PROCESS_FEED, function(FeedProcessEvent $event) {

});
```

### The `stepBeforeElementMatch` event

Triggered for each feed item, plugins can get notified before existing elements are tried to be matched.

```php
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_MATCH, function(FeedProcessEvent $event) {

});
```

### The `stepBeforeElementSave` event

Triggered for each feed item, plugins can get notified before the prepared element is about to be saved.

```php
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_BEFORE_ELEMENT_SAVE, function(FeedProcessEvent $event) {

});
```

### The `stepAfterElementSave` event

Triggered for each feed item, plugins can get notified after the prepared element has been saved.

```php
use verbb\feedme\events\FeedProcessEvent;
use verbb\feedme\services\Process;
use yii\base\Event;

Event::on(Process::class, Process::EVENT_STEP_AFTER_ELEMENT_SAVE, function(FeedProcessEvent $event) {

});
```
