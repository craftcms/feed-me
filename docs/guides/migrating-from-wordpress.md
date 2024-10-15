# Migrating from WordPress

Migrating from WordPress to Craft can be challenging, but Feed Me greatly simplifies the process of getting data back into Craft once it’s been exported.

:::tip
We cannot provide support for exporting WordPress content, but we are happy to help troubleshoot importing issues!
:::

## Create export from WordPress

### Plugin Exporters

The first step is to get your data out of WordPress. We’ll assume you’re using the free [WP All Export](https://wordpress.org/plugins/wp-all-export/) plugin. It'll produce an XML file of your content and supports all native fields, Advanced Custom Fields, WooCommerce, custom taxonomies, and custom post types.

::: tip
Use one or more of the tutorials to set up exports for the data you need. Keep in mind that you can import content in multiple stages via different feeds—you don't have to import (or export) _everything_ at once!
:::

The “shape” of your exported data is flexible. As long as 

### WP JSON

If you have a relatively vanilla WordPress installation, you may be able to use the [built-in REST API](https://learn.wordpress.org/tutorial/using-the-wordpress-rest-api/) to dynamically retrieve your site’s content as JSON.

## Setting up Feed Me

Follow the guide to [Importing Entries](importing-entries.md), using the URL or file from the previous step.

::: tip
Create a custom field to store legacy WordPress post IDs so that you can match them up, later! When imported into Craft, your posts (now _entries_) will get new IDs that aren't tied to the import data at all.

Subsequent imports (say, to associate authors with posts) can use that ID as the **Unique Identifier**.
:::
