# Element Types

### The `registerFeedMeElements` event
Plugins can register their own elements.

```php
use verbb\feedme\events\RegisterFeedMeElementsEvent;
use verbb\feedme\services\Elements;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_REGISTER_FEED_ME_ELEMENTS, function(RegisterFeedMeElementsEvent $e) {
    $e->elements[] = MyElement::class;
});
```

### The `beforeParseElement` event
Plugins can get notified before a element's data has been parsed.

```php
use verbb\feedme\events\ElementEvent;
use verbb\feedme\services\Elements;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_BEFORE_PARSE_ELEMENT, function(ElementEvent $e) {

});
```

### The `afterParseElement` event
Plugins can get notified after a element's data has been parsed.

```php
use verbb\feedme\events\ElementEvent;
use verbb\feedme\services\Elements;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_AFTER_PARSE_ELEMENT, function(ElementEvent $e) {

});
```
