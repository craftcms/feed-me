# Creating your Feed

Each field is fairly self-explanatory, but any additional information is provided below.

### Name

Setup a name for you feed, so you can easily keep track of what you're importing.

### Feed URL

Provide the URL for your feed. This can be an absolute URL, relative (to the web root) and make use of any [aliases](https://docs.craftcms.com/v3/config/#aliases).

### Feed Type

Its important to set the Feed Type to match the type of data you're importing. While its optional to pick `RSS` or `ATOM`, you'll find that choosing these options will pre-set the [Primary Element](docs:feature-tour/creating-your-feed#primary-element) field below.

### Element Type

Select the Element you wish to import your feed content into. For the [Free](/craft-plugins/feed-me/pricing) version, you'll only be able to import into Entries, but [Pro](https://verbb.io/craft-plugins/feed-me/pricing) will allow you to select Assets, Categories, Users, Commerce Products.

### Sites

If you're running _Craft Pro_, and have a Multi Site setup, you'll have an additional field to select which Site to import into. Only the selected Site will have content imported, leaving all others untouched.

### Import Strategy

The Import Strategy tells Feed Me how to act when (or if) it comes across elements that are similar to what you're importing. If you've imported your content once, there will very likely be elements with the same title or content as what you're trying to import.

For example - you have an existing entry called "About Us", but you also have an item in your feed with exactly the same title. You should tell Feed Me what to do when it comes to processing this entry in your feed. Do you want to update that same entry, or add a new one?

You can select from any combination of the following:

Attribute | Description
--- | ---
Create new elements | Adds new elements if they do not already exist. If an element does exist, it simply skips over it, leaving it unchanged.
Update existing elements | Updates elements that match the Unique Identifier (next step). If no existing element to update, it won't create it unless you select `Create new elements`.
Disable missing elements | Disables elements that are not updated by this feed.
Delete missing elements | Deletes elements that are not updated by this feed. **Be careful when deleting**.

### Passkey

A generated, unique string to increase security against imports being run inadvertently. This is mainly used when triggering an import via the direct feed link.

### Backup

Enable a backup of your database to be taken on each import. Please note the [performance implications](docs:support/troubleshooting#performance) when switching this on.

* * *

Click on the _Save & Continue_ button to be taken to the [Field Mapping](docs:feature-tour/field-mapping) screen, or press _Save_ to continue editing this screen.

* * *