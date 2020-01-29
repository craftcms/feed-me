# Element Types

### The `registerFeedMeElements` event
Plugins can register their own elements.

```php
use craft\feedme\events\RegisterFeedMeElementsEvent;
use craft\feedme\services\Elements;
use yii\base\Event;

Event::on(Elements::class, Elements::EVENT_REGISTER_FEED_ME_ELEMENTS, function(RegisterFeedMeElementsEvent $e) {
    $e->elements[] = MyElement::class;
});
```

### The `beforeParseAttribute` event
Plugins can get notified before a element's attribute has been parsed.

```php
use craft\feedme\base\Element;
use craft\feedme\events\ElementEvent;
use yii\base\Event;

Event::on(Element::class, Element::EVENT_BEFORE_PARSE_ATTRIBUTE, function(ElementEvent $e) {

});
```

### The `parseAttribute` event
Plugins can get notified after a element's attribute has been parsed.

```php
use craft\feedme\base\Element;
use craft\feedme\events\ElementEvent;
use yii\base\Event;

Event::on(Element::class, Element::EVENT_AFTER_PARSE_ATTRIBUTE, function(ElementEvent $e) {
    $parsedValue = $e->parsedValue;
});
```
