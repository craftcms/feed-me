# Migrating from WordPress

Migrating from WordPress to Craft can be challenging—but the bulk of the effort tends to be getting your content _out_ of WordPress (and various plugins’ content storage systems) and into a format that Feed Me understands.

::: tip
Consider reviewing the [updated recommendations](https://craftcms.com/knowledge-base/for-wordpress-devs) for Craft 5.x, which makes use of our new [dedicated import tool](https://github.com/craftcms/wp-import).

While we are happy to support Feed Me and the `wp-import` extension, we are unable to offer help with third-party WordPress export tools. This guide provides some general recommendations, but may not be a turn-key solution for every WordPress installation!
:::

## Create export from WordPress

### Plugin Exporters

The first step is to get your data out of WordPress. We’ll assume you’re using the free [WP All Export](https://wordpress.org/plugins/wp-all-export/) plugin. It'll produce an XML file of your content and supports all native fields, Advanced Custom Fields, WooCommerce, custom taxonomies, and custom post types.

Use one or more of the tutorials to set up exports for the data you need. Keep in mind that you can import content in multiple stages via different feeds—you don't have to import (or export) _everything_ at once!

::: warning
Make sure the WordPress `ID` is present for every record you export.
:::

### WP JSON

If you have a relatively vanilla WordPress installation, you may be able to use the [built-in REST API](https://learn.wordpress.org/tutorial/using-the-wordpress-rest-api/) to dynamically retrieve your site’s content as JSON.

### Feed Me Settings

Follow the guide to [Importing Entries](importing-entries.md), using the URL or file from the previous step. You may need to run multiple imports for different WordPress resources, mapped to the corresponding Craft element types—like _media_ ([assets](importing-assets.md)), _users_, and _taxonomies_.

### How to Handle IDs

When importing records from WordPress, they will receive new IDs. Create a custom field for legacy WordPress `ID`s and attach it to every import target (entry type, asset volume, etc.). When you set up your feed, map the WordPress ID to that custom field. This will help match up content if you need to re-import it again at a later date—and to connect things like posts and categories. 

To illustrate, the WordPress post with `ID` `123`…

```php{1}
$post = get_post(123);
get_the_title($post);
// -> "Importing into Craft CMS" 
```

…will probably not end up with the same `id` in Craft…

```twig{3}
{% set post = craft.entries()
  .section('posts')
  .id(123)
  .one() %}

{{ post.title }}
{# -> "How we use Advanced Custom Fields" 🚫 #}
```

By assigning the post `ID` to a custom field, you can look them up using the corresponding query method for the custom field:

```twig{3}
{% set post = craft.entries()
  .section('posts')
  .legacyWpId(123)
  .one() %}

{{ post.title }}
{# -> "Importing into Craft CMS" ✅ #}
```
