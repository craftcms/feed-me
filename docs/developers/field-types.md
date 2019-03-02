# Field Types

### The `registerFeedMeFields` event
Plugins can register their own field.

```php
use verbb\feedme\events\RegisterFeedMeFieldsEvent;
use verbb\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_REGISTER_FEED_ME_FIELDS, function(RegisterFeedMeFieldsEvent $e) {
    $e->fields[] = DataType::class;
});
```

### The `beforeParseField` event
Plugins can get notified before a field's data has been parsed.

```php
use verbb\feedme\events\FieldEvent;
use verbb\feedme\services\Fields;

Event::on(Fields::class, Fields::EVENT_BEFORE_PARSE_FIELD, function(FieldEvent $e) {

});
```

### The `afterParseField` event
Plugins can get notified after a field's data has been parsed.

```php
use verbb\feedme\events\FieldEvent;
use verbb\feedme\services\Fields;

Event::on(Fields::class, Fields::EVENT_AFTER_PARSE_FIELD, function(FieldEvent $e) {
    $parsedValue = $e->parsedValue;
});
```
