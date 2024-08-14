# Creating your Feed

Each field is fairly self-explanatory, but any additional information is provided below.

### Name

Setup a name for you feed, so you can easily keep track of what you're importing.

### Feed URL

Provide the URL for your feed. This can be complete URL, an absolute path beneath your site’ web root, a filesystem path, or an [alias](https://craftcms.com/docs/5.x/configure.html#aliases) thereof. These are all valid settings for a **Feed URL**:

- `https://api.myservice.com/v1/products`
- `/uploads/finishes.json`
- `@web/artists.xml`
- `/tmp/crm-export/daily-sales-report.csv`
- `@root/private/subscribers.json`

::: tip
If you use the `@web` alias in any URLs, make sure it's defined for console requests.
:::

### Feed Type

Set the **Feed Type** to match the type of data you're importing. Your options are:

- ATOM
- CSV
- Google Sheet
- JSON
- RSS
- XML

:::tip
When using Google Sheet as your feed type, consult the [Google Sheets API](https://developers.google.com/sheets/api/guides/concepts) docs. Your URL should include a `key` value for your API key. For example:

`https://sheets.googleapis.com/v4/spreadsheets/xxxxxxxxxxxxxxxx/values/SheetName?key=xxxxxxxxxxxxxxxx`

:::

### Element Type

Select the [element type](../content-mapping/element-types.md) you wish to import your feed content into.

### Target Site

Multi-site Craft installations will display an additional **Target Site** setting where you can select which site the elements should be initially saved in. The content will get propagated to your other sites from there, according to your fields’ [Translation Method](https://craftcms.com/docs/5.x/system/fields.html#translation-methods).

### Import Strategy

The **Import Strategy** tells Feed Me how to act when (or if) it comes across elements that are similar to what you’re importing. If you’ve imported your content once, there will very likely be elements with the same title or content as what you're trying to import.

::: tip
The actual matching behavior is determined by a [unique identifier](field-mapping.md#unique-identifiers), which you’ll configure in a moment.
:::

For example: you have an existing entry called “About Us,” but you also have an item in your feed with exactly the same title. You should tell Feed Me what to do when it comes to processing this entry in your feed. Do you want to update that same entry, or add a new one?

You can select from any combination of the following:

Attribute | Description
--- | ---
**Create new elements** | Adds new elements if they do not already exist (as determined by a _unique identifier_). If an element _does_ exist, it will only be updated if **Update existing elements** is enabled.
**Update existing elements** | Updates elements that match the _unique identifier_. If no existing element matches, one will be only be created if **Create new elements** is also enabled.
**Disable missing elements** | Disables elements that are not updated by this feed.
**Disable missing elements in the target site** | Disables elements that are not updated by this feed, but only in the feed’s [target site](#target-site).
**Delete missing elements** | Deletes elements that are not updated by this feed. **Be careful when deleting**.
**Update search indexes** | Whether search indexes should be updated.

### Passkey

A generated, unique string to increase security against imports being run inadvertently. This is mainly used when [triggering an import](trigger-import-via-cron.md) via HTTP using the direct feed link.

### Backup

Enable a backup of your database to be taken on each import. Please note the [performance implications](../troubleshooting.md#performance) when switching this on.

### Set Empty Values

When enabled, empty values in a feed item are considered valid and will clear the corresponding fields when your [Import Strategy](#import-strategy) includes _update existing elements_. When disabled, empty values are ignored or treated as unchanged.

Keys omitted from a feed item are not considered “empty” and will not clear values on existing entries.

* * *

Click **Save & Continue** to be taken to the [Primary Element](primary-element.md) screen, or **Save** to continue making changes on this screen.
