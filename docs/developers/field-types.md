# Field Types

For third-party Field Type support, you will need to create a few files, and implement certain functionality. This will depend largely on what your Field Type does exactly, and how it stores its data, but this guide will aim to be as generic as possible.

### Getting started

You'll need to create the following directories/files for your plugin:

```
myplugin/
    integrations/
        feedme/
            fields/
                MyPlugin_ExampleFeedMeFieldType.php
    templates/
        _integrations/
            feedme/
                fields/
                    myplugin_example.html
```

:::tip
You're free to structure these files exactly how you want, as these paths are configurable, but keeping them in an `integrations` folder keeps things organised. Its your call!
:::

Then, go to your `MyPlugin.php` main plugin file, and add the following:

```php
public function init()
{
    // The rest of your init() function

    // Import your Feed Me Field Type implementation
    Craft::import('plugins.myplugin.integrations.feedme.fields.MyPlugin_ExampleFeedMeFieldType');
}

// Tell Feed Me your plugin supports the following Field Types
public function registerFeedMeFieldTypes()
{
    return array(
        new MyPlugin_ExampleFeedMeFieldType(),
    );
}
```

:::tip
If your plugin has multiple fieldtypes, add each of them to the `registerFeedMeFieldTypes` array.
:::

### Field Type Class

Your newly created `MyPlugin_ExampleFeedMeFieldType` PHP class is where the logic for your support for Feed Me sits. This class must extend `BaseFeedMeFieldType`.

An example implementation:

```php
namespace Craft;

class MyPlugin_ExampleFeedMeFieldType extends BaseFeedMeFieldType
{
    // Templates
    // =========================================================================

    public function getMappingTemplate()
    {
        return 'myplugin/_integrations/feedme/fields/myplugin_example';
    }

    // Public Methods
    // =========================================================================

    public function prepFieldData($element, $field, $data, $handle, $options)
    {
        // Your Field Type logic goes here...
    }
}
```

:::tip
Add as many private or public functions to this class as you like - but you **must** implement `getMappingTemplate()` and `prepFieldData()`.
:::

#### getMappingTemplate

This function returns a HTML template for use on the [Field Mapping](docs:feature-tour/field-mapping) screen. This is useful if your field type requires special processing, or is more than a simple text-field.

For example, consider the Table field type - mapping data in your feed to the Table field type doesn't make sense. Instead, you want to map data to individual columns in your Table field. The `getMappingTemplate()` function allows you to create your own HTML for your field type.

As a practical example, we'll use an Address Field Type, where you have a street address, city, state, etc. Refer to the below example:

```twig
{# Loop through all sub-fields for your field type #}
{% set subfields = ['street','city'] %}

{% for subfield in subfields %}
    {% set optionLabel  = field.name ~ ' (' ~ subfield ~ ')' %}
    {% set optionLabelHandle = field.handle ~ '[' ~ subfield ~ ']' %}
    {% set optionHandle = field.handle ~ '--' ~ subfield %}

    <tr>
        <td class="col-field">
            <div class="field">
                <div class="heading">
                    <label>{{ optionLabel }}</label>

                    <div class="instructions">
                        <code>{{ optionLabelHandle }}</code>
                    </div>
                </div>
            </div>
        </td>

        <td class="col-map">
            {% namespace 'fieldMapping' %}
                {{ forms.selectField({
                    id: optionHandle,
                    name: optionHandle,
                    value: feed.fieldMapping[optionHandle] ?? '',
                    options: feedData,
                }) }}
            {% endnamespace %}
        </td>

        <td class="col-default">
            <div class="default-fields">
                {% namespace 'fieldDefaults' %}
                    {{ forms.textField({
                        id: field.handle,
                        name: field.handle,
                        value: feed.fieldDefaults[optionHandle] ?? '',
                    }) }}
                {% endnamespace %}
            </div>
        </td>
    </tr>
{% endfor %}
``` 

:::tip
If your fieldtype stores its data as an array, ensure the name contains `--`, and Feed Me will take care of the rest.
:::

Templates have access to the following variables:

- `field` - The Field Model for your field type.
- `feed` - The Feed Model for this feed.
- `feedData` - The actual data for this feed.

### prepFieldData

When the feed is being processed, you'll likely want to create logic on how to treat your data coming from the feed. This is particularly crucial if your Field Type has sub-fields.

```php
public function prepFieldData($element, $field, $data, $handle, $options)
{
    // Initialize content array
    $content = array();

    foreach ($data as $subfieldHandle => $subfieldData) {
        // Set value to subfield of correct address array
        $content[$subfieldHandle] = $subfieldData;
    }

    // Return data
    return $content;
}
```  

#### Parameters

- `$element` - The Element Type this feed is importing into.
- `$field` - The Field Model for your field type.
- `$data` - The actual data being mapped to your field.
- `$handle` - The handle of the field for the element type.
- `$options` - Additional options, should you need them.

The above example shows data being looped over, because it has sub-fields as per our mapping example above. You must return content in a way that your Field Type is able to interpret - be it a single string value, or an array (if you have sub-fields).

The `$data` variable passed into this function would look like:

```php
$data = array(
    street => '42 Wallaby Way',
    city => 'Sydney',
);
```
