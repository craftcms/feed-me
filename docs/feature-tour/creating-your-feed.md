# Creating your Feed

Each field is fairly self-explanatory, but any additional information is provided below.

### Name

![Feedme Setup 1](/uploads/plugins/feed-me/feedme-setup-1.png)

Setup a name for you feed, so you can easily keep track of what you're importing.

### Feed URL

![Feedme Setup 2](/uploads/plugins/feed-me/feedme-setup-2.png)

Provide the URL for your feed. This can be an absolute URL, relative (to the web root) and use any `environmentVariables` you have in your `general.php` config.

### Feed Type

![Feedme Setup 3](/uploads/plugins/feed-me/feedme-setup-3.png)

Its important to set the Feed Type to match the type of data you're importing. While its optional to pick `RSS` or `ATOM`, you'll find that choosing these options will pre-set the [Primary Element](/craft-plugins/feed-me/docs/feature-tour/creating-your-feed#primary-element) field below.

### Primary Element

![Feedme Setup 4](/uploads/plugins/feed-me/feedme-setup-4.png)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss>
    <channel>
        <item>
            <title>My Title</title>
            <slug>my-title</slug>
        </item>

        <item>
            <title>Another Title</title>
            <slug>another-title</slug>
        </item>
    </channel>
</rss>
```

Your Primary Element would be `item`. This is the node thats repeatable, and you can usually pick it as it'll be the node thats one level above the content you want to import (`title` and `slug` in this case). In the JSON example, you'll see its a plain array, but the same applies.

:::tip
Notably, the above example XML is actually an RSS feed. Selecting `RSS` in the Feed Type will auto-set the Primary Element to `item`.
:::

### Element Type

![Feedme Setup 5](/uploads/plugins/feed-me/feedme-setup-5.png)

Select the Element you wish to import your feed content into. For the [Free](/craft-plugins/feed-me/pricing) version, you'll only be able to import into Entries, but [Pro](/craft-plugins/feed-me/pricing) will allow you to select Assets, Categories, Users, Commerce Products.

### Locales

![Feedme Setup 6](/uploads/plugins/feed-me/feedme-setup-6.png)

If you're running _Craft Pro_, and have Locales setup, you'll have an additional field to select which Locale to import into. Only the selected Locale will have content imported, leaving all others untouched.

### Import Strategy

![Feedme Setup 7](/uploads/plugins/feed-me/feedme-setup-7.png)

The Import Strategy tells Feed Me how to act when (or if) it comes across elements that are similar to what you're importing. If you've imported your content once, there will very likely be elements with the same title or content as what you're trying to import.

For example - you have an existing entry called "About Us", but you also have an item in your feed with exactly the same title. You should tell Feed Me what to do when it comes to processing this entry in your feed. Do you want to update that same entry, or add a new one?

You can select from any combination of the following:

#### Create new elements

Adds new elements if they do not already exist. If an element does exist, it simply skips over it, leaving it unchanged.

#### Update existing elements

Updates elements that match the Unique Identifier (next step). If no existing element to update, it won't create it unless you select `Create new elements`.

#### Delete old elements

Deletes elements that are not updated by this feed. **Be careful when deleting**.

### Passkey

![Feedme Setup 8](/uploads/plugins/feed-me/feedme-setup-8.png)

A generated, unique string to increase security against imports being run inadvertently. This is mainly used when triggering an import via the direct feed link.

### Backup

![Feedme Setup 9](/uploads/plugins/feed-me/feedme-setup-9.png)

Enable a backup of your database to be taken on each import. Please note the [performance implications](/craft-plugins/feed-me/docs/support/troubleshooting#performance) when switching this on.

* * *

With your fields populated, you should have a final result similar to the below screenshot.

![Feedme Setup](/uploads/plugins/feed-me/feedme-setup.png)

Click on the _Save & Continue_ button to be taken to the [Field Mapping](/craft-plugins/feed-me/docs/feature-tour/field-mapping) screen, or press _Save_ to continue editing this screen.

* * *