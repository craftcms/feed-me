# Using in your Templates

While you can create a feed queue job to insert data as elements, there are times when you may prefer to capture feed data on-demand. You can easily do this in your Twig templates using Feed Me’s API.

Feeds are cached for performance (default to 60 seconds), which can be set by a tag parameter, or in the plugin settings.

```twig{5}
{% set params = {
    url: 'http://path.to/feed/',
    type: 'xml',
    element: 'item',
    cache: 60,
} %}

{% set feed = craft.feedme.feed(params) %}

{% for node in feed %}
    {# Your template code goes here #}
{% endfor %}
```

::: danger
Do not issue requests to user-supplied URLs! If you must parameterize a feed URL, validate the incoming data, first.
:::

#### Parameters

- `url` (string, required) - URL or path to the feed.
- `type` (string, optional) - The type of feed you're fetching data from. Valid options are json or xml (defaults to xml).
- `element` (string, optional) - Element to start feed from. Useful for deep feeds.
- `cache` (bool or number, optional) - Whether or not to cache the request. If true, will use the default as set in the plugin settings, or if a number, will use that as its duration. Setting to false will disable cache completely.

### Example template code

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<entries>
    <entry>
        <title>Monday</title>
        <item>
            <title format="html">Event 1</title>
            <type>All-day</type>
        </item>
    </entry>
    
    <entry>
        <title>Tuesday</title>
        <item>
            <title format="html">Event 2</title>
            <type>Half-day</type>
        </item>
    </entry>
</entries>
```

With the above example XML, we would use the following Twig code to loop through each `entry` to extract its data.

```twig
{% set params = {
    url: 'http://path.to/feed/',
    type: 'xml',
    element: 'entry',
    cache: 60,
} %}

{% set feed = craft.feedme.feed(params) %}

{% for node in feed %}
    Title: {{ node.title }}
    Item: {{ node.item.title['@'] }}
    Item Format: {{ node.item.title['@format'] }}
    Type: {{ node.item.type }}
{% endfor %}

{# Producing the following output #}
Title: Monday
Item: Event 1
Item Format: html
Type: All-day

Title: Tuesday
Item: Event 2
Item Format: html
Type: Half-day
```

:::tip
There's a special case for XML-based feeds, which is illustrated above when attributes are present on a node. To retrieve the node value, use `['@']`, and to fetch the attribute value, use `['@attribute_name']`.
:::
