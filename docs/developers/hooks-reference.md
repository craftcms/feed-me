# Hooks Reference

Feed Me provides several hooks that give other plugins the chance to interact with the plugin.

### registerFeedMeFieldTypes

```php
public function registerFeedMeFieldTypes()
{
    return array(
        new PluginName_FieldTypeFeedMeFieldType(),
    );
}
```

Learn more about [Field Types](/craft-plugins/feed-me/docs/developers/field-types).

### registerFeedMeDataTypes

```php
public function registerFeedMeDataTypes()
{
    return array(
        new PluginName_CsvFeedMeDataType(),
    );
}
```

Learn more about [Data Types](/craft-plugins/feed-me/docs/developers/data-types).

### registerFeedMeElementTypes

```php
public function registerFeedMeElementTypes()
{
    return array(
        new PluginName_ElementFeedMeElementType(),
    );
}
```

Learn more about [Element Types](/craft-plugins/feed-me/docs/developers/element-types).