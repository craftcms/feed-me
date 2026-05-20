# Field Types

### The `registerFeedMeFields` event

Plugins developers can add support for their field types via the `craft\feedme\services\Fields::EVENT_REGISTER_FEED_ME_FIELDS` event:

```php{6}
use craft\feedme\events\RegisterFeedMeFieldsEvent;
use craft\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_REGISTER_FEED_ME_FIELDS, function(RegisterFeedMeFieldsEvent $e) {
    $e->fields[] = my\plugin\feedme\fields\MyFieldType::class;
});
```

Your `MyFieldType` class must extend `craft\feedme\base\Field`, and implement at least the following properties and methods:

```php
namespace my\plugin\feedme\fields;

use craft\feedme\base\Field;
use my\plugin\fields\MyFieldType;

class MyFieldType extends Field
{
    /**
     * @var string
     */
    public static string $name = 'My Custom Field Type';

    /**
     * @var string
     */
    public static string $class = MyFieldType::class;

    /**
     * @inheritdoc
     */
    public function getMappingTemplate(): string
    {
        // Return a valid template path for your plugin:
        return 'my-plugin/feedme/mapping-template';
    }

    /**
     * @inheritdoc
     */
    public function parseField(): mixed
    {
        // Take the incoming item’s data...
        $data = $this->feedData;

        // 
        $value = 
    }
}
```

Your mapping template should include HTML inputs for any options that affect how incoming data is parsed—like delimiters or formats. Those inputs’ `name` attributes should begin with `options`, and their values should be retrieved from the same property of the `feed.fieldMapping.options`:

```twig
{% import '_includes/forms' as forms %}

{% extends 'feed-me/_includes/fields/_base' %}

{% block extraSettings %}
    {{ forms.selectField({
        name: 'options[delimeter]',
        value: hash_get(feed.fieldMapping, "#{optionsPath}.delimeter", ','),
        options: [
            { value: ',', label: 'Comma (,)'|t('my-plugin') },
            { value: '/', label: 'Slash (/)'|t('my-plugin') },
            { value: '|', label: 'Pipe (|)'|t('my-plugin') },
        ],
    }) }}
{% endblock %}
```

Inputs will be namespaced by the extended template. Use the `hash_get()` helper function to dynamically retrieve preexisting settings with the provided `optionsPath` variable.

::: tip
See the built-in [field types](https://github.com/craftcms/feed-me/blob/6.x/src/fields) and [field settings templates](https://github.com/craftcms/feed-me/blob/6.x/src/templates/_includes/fields) to see how we handle different types of data.
:::

### The `beforeParseField` event

Plugins can get notified before a field's data has been parsed.

```php
use craft\feedme\events\FieldEvent;
use craft\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_BEFORE_PARSE_FIELD, function(FieldEvent $e) {

});
```

### The `afterParseField` event

Plugins can get notified after a field's data has been parsed.

```php
use craft\feedme\events\FieldEvent;
use craft\feedme\services\Fields;
use yii\base\Event;

Event::on(Fields::class, Fields::EVENT_AFTER_PARSE_FIELD, function(FieldEvent $e) {
    $parsedValue = $e->parsedValue;
});
```
