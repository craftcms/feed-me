# Migrating from ExpressionEngine

Migrating from ExpressionEngine to Craft is relatively straightforward in most cases, due to the similarities between systems. This guide will aim to provide a walkthrough on a generic migration process. Of course - just as each website is different, so too will each migration.

This guide is also aimed at ExpressionEngine 2.x. We will not be offering a guide for ExpressionEngine 3.x.

:::tip
We are unable to offer any dedicated support for exporting out of ExpressionEngine. Its essentially up to you how you get your data, and this guide is for reference only.
:::

### Create export from ExpressionEngine

The first step is to create a template in ExpressionEngine to export your content. You could create a template per-channel if you prefer, but for ease-of-use, we create a single file, passing in the desired channel as a query parameter.

Copy and paste the contents of [this Gist](https://gist.github.com/engram-design/5fbe54ef0abb15e3ff6f667291098464) into a new template in ExpressionEngine. Lets call it `export.html`.

:::tip
Make sure you enable PHP execution on this template.
:::

Now, visit the following URL (for your ExpressionEngine site):

```
http://my.ee.site/export?id=9
```

With `id` being the ID of your channel. This template code will loop through all custom fields for this channel, grab the data from the contents table, and output as JSON. Either save the contents of this page as `*.json` file, or save the URL for use with Feed Me.

### Settings up Feed Me
Follow the guide to [Importing Entries](/craft-plugins/feed-me/docs/guides/importing-entries), using the URL or file from the previous step.