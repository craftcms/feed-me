# Field Mapping

Now that you’ve told Feed Me where your data comes from, it’s time to define how individual items in the feed map to new or existing elements in Craft.

While the specifics will vary widely depending on your content model (and the structure of the incoming data), the same pattern applies to most map configuration:

1. Find the _target_ field in the **Field** column;
1. Choose a _source_ for that field’s data from the menu in its row’s **Feed Element** column;
1. Customize options for the type of data being imported;
1. (Optional) Set a static or dynamic [default value](#default-values);

## Native Fields

The native fields you have available to map against depend on the element type you’ve selected as the target for the import—for example, entries support a **Title**, **Slug**, **Parent**, **Post Date**, **Expiry Date**, **Status**, and **Author** in addition to the [custom fields](#custom-fields) attached via its field layout.

::: tip
Native fields have similar options to [custom fields](#custom-fields), so we’ll only cover the novel ones, in this section.
:::

### Element IDs

You can map data in your feed to the element ID, which is useful if you are certain which element you want a given feed item to update. This can be when updating for content in other locales, or bulk-updating existing items.

::: danger
Do not use this when importing “new” data! Content from another system (ExpressionEngine, WordPress, etc.) will _not_ have the same IDs as their corresponding elements in Craft, by virtue of how records are created in the database. If you specify the _wrong_ element ID (deliberately or coincidentally), you run the risk of updating completely unrelated content (i.e. an asset when you meant to update an entry).

There are only two situations in which setting IDs is recommended:
- When re-importing data that was exported from Craft, then modified;
- Importing or synchronizing data from external systems that already track Craft element IDs;

In most cases, incoming data should be matched based on a different [unique identifier](#unique-identifiers).
:::

## Custom Fields

Like native fields, each custom field type’s configuration options depend on what kind of data it stores.

### Scalar Data

Text, numbers, booleans, and other basic data types require no additional configuration. Color, dropdown, email, lightswitch, money, radio, and URL fields all use “scalar” values.

### Dates

Feed Me can parse [most date formats](https://www.php.net/manual/en/function.strtotime.php), but to handle cases where it may be ambiguous (i.e. `01-02-2023`), you can lock the mapping to a specific pattern.

### Relational Fields

When setting up related content through an assets, categories, entries, tags, or users field, you will be asked how Feed Me should locate the referenced element(s).

For example, if you were importing a list of AKC winners that contained a `breed` property with values like `Dachshund` or `Greyhound`, you might tell Feed Me to look up existing _Breed_ entries by their **Title**, and to **Create entries if they do not exist**. The same holds true for other element types and their corresponding relational field types.

#### Nested Fields

When importing relational data, you have an opportunity to map values onto those elements’ fields, as well. Enable **Element fields (x)** to expand controls for those nested fields.

::: warning
Keep in mind that nested field values will be applied uniformly to all relations.
:::

### Matrix

See the [Importing into Matrix](../guides/importing-into-matrix.md) guide to learn more about this special field type.

### Plugin Fields

Feed Me comes with support for the following plugin-provided field types:

Field Type | Developer
--- | ---
[Calendar Events](https://plugins.craftcms.com/calendar) | Solspace
[Commerce Products](https://plugins.craftcms.com/commerce) | Pixel & Tonic
[Commerce Variants](https://plugins.craftcms.com/commerce) | Pixel & Tonic
[Entries Subset](https://plugins.craftcms.com/entriessubset) | Nathaniel Hammond
[Google Maps](https://plugins.craftcms.com/google-maps) | Double Secret Agency
[Linkit](https://plugins.craftcms.com/linkit) | Pressed Digital
[Simplemap](https://plugins.craftcms.com/simplemap) | Ether Creative
[Super Table](https://plugins.craftcms.com/supertable) | Verbb
[Typed Link](https://plugins.craftcms.com/typedlinkfield) | Sebastian Lenz

::: tip
Other fields that store simple text values (like [Redactor](https://plugins.craftcms.com/redactor)) will work automatically.
:::

## Default Values

When the source for a native or custom field is set to “Use default value,” you may provide a value in the third column that will supersede any default value defined by the field itself. If `parseTwig` is enabled in your [Configuration](../get-started/configuration.md), textual fields are treated as Twig “object templates,” and have access to other fields on the element you're importing:

```txt
{title} was last imported on {{ now | date }}
```

## Unique Identifiers

It's important to select a **unique identifier** for your feed to assist with the **Import Strategy** you’ve chosen. When comparing against existing entries, it will compare the fields you select here with the data in your feed. In addition to the element’s native fields (like title, slug, status, or ID), you may use custom field values for matching.

::: warning
There are _some_ limitations to the matching engine, though—it will not work for content stored in Matrix and other nested, Matrix-like fields such as Super Table and Neo which can’t be easily or reliably serialized.
:::

::: danger
If data that is used as part of a unique identifier is altered between imports by a user (or any other means—including a different import), Feed Me may not be able to match it again! When combined with the **Delete missing elements** [import strategy](creating-your-feed.md#import-strategy), this can result in inadvertent data loss.
:::
