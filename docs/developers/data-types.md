# Data Types

### The `registerFeedMeDataTypes` event
Plugins can register their own data types.

```php
use craft\feedme\events\RegisterFeedMeDataTypesEvent;
use craft\feedme\services\DataTypes;
use yii\base\Event;

Event::on(DataTypes::class, DataTypes::EVENT_REGISTER_FEED_ME_DATA_TYPES, function(RegisterFeedMeDataTypesEvent $e) {
    $e->dataTypes[] = MyDataType::class;
});
```
