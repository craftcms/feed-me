# Feed Me 2.0 - Updates

We're hard at work on the next version of Feed Me, with a massive amount of updates, fixes and improvements. Stay tuned to the [2.0 branch](https://github.com/engram-design/FeedMe/tree/2.0.0) for the beta.

# Feed Me

Feed Me is a Craft plugin to make it easy to import entries and entry data from XML, RSS, ATOM or JSON feeds. Feeds can be setup as a task in Craft's Control Panel, or called on-demand for use in your twig templates.

A common use-case for this plugin is to consume external feeds (news, events), but can also be used as a once-off task for importing content when migrating from other sites.

<img src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/main.png" />


## Features

- Import data from XML, RSS, ATOM or JSON feeds.
- Feeds are saved to allow easy re-processing on-demand, or to be used in a Cron job.
- Map feed data to your entry fields. See Supported Fieldtypes.
- Duplication handling - control what happens when feeds are processed again.
- Uses Craft's Task service to process feeds in the background.
- Database backups before each feed processing.
- Troubleshoot feed processing issues with logs.
- Grab feed data directly from your twig templates.
- Craft 2.5 compatible.


## Install

- Add the `feedme` directory into your `craft/plugins` directory.
- Navigate to Settings -> Plugins and click the "Install" button.

**Plugin options**

- Change the plugin name as it appear in the CP navigation.
- Set the default cache (for calls using the template tag only).
- Enable or disable specific tabs for Feed Me.


## Usage

Head to the Feed Me page using the CP navigation, and click the New Feed button.

Enter the required details to configure your feed:

- Name this feed something useful so you'll remember what it does.
- The actual URL to your feed.
- Set the Feed Type to assist with mapping the correct elements.
- The Primary Element reflects which node in the feed your data sits.
- Select the Section and Entry Type for where you want to put the feed data.
- Decide on an import strategy: how you'd like to handle duplicate feed items (if you're going to be re-running this feed).

<img src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/mapping_1.png" />

Click on the `Save and continue` to be taken to the field mapping screen. Here, you select what data from the feed you wish to capture, and what fields to map it to, depending on your Section and Entry Type selection. Here you'll be able to choose which fields (can be more than one) you'd like to compare to determine if a feed item is a duplicate.

You must map data to at least the Title field, or any other required field for your entry.

<img src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/mapping_2.png" />

Save the feed for later, or start the import.


### Supported Fieldtypes

Feed Me supports mapping data from your feeds to the following Fieldtypes:

**Craft**

- Assets
- Categories
- Checkboxes
- Color
- Date/Time
- Dropdown
- Entries
- Lightswitch
- Matrix
- Multi-Select
- Number
- Plain Text
- Position Select
- Radio Buttons
- Rich Text
- Table
- Tags
- Users

**Third-Party**

- Super Table
- SmartMap


###Element Creation

For certain elements, it may be benefitial to create the element's data, if not already created. Like if an Asset doesn't exist in your Assets collection, upload it. Similarly with Categories, and other fields.

Currently, Feed Me handles the following applicable fields in these ways:

**Assets:** Only supports mapping existing assets to this entry.

**Categories:** Are created if they do not exist, or mapped if they do.

**Entries:** Only supports mapping existing entries to this entry. The feed field must contain either the Title or Slug of the entry to successfully map. 

**Tags:** Are always created.

**Users:** Only supports mapping existing users to this entry.

Internally, Feed Me uses Craft's element search to match against the value in your feed for an element. For example, if you have `my_filename.png` as a value in your feed, and you are mapping to an Asset, ensure that searching through the Assets index screen actually returns what you expect.

For troubleshooting, ensure you have completed the Rebuild Search Indexes task.

We plan to include options for whether you would like to do this on a per-field basis.


### Import strategy

When running the feed task multiple times, there may or may not be the same data present in the feed as the last time you ran the task. To deal with this, you can control what happens to old (and new) entries when the feed is processed again.

You may choose multiple fields to determine if an entry is a duplicate. Most commonly, you'll want to compare the `Title` field, but can be any fields you require.

#### Strategy options:

**Add Entries:**

Existing entries will be skipped and left untouched, new entries however, will be added to the section. Use case: Feed aggregation, blog entries, etc.

_"I want to keep existing entries untouched but add new ones."_

**Update Entries**

Existing entries will have their fields updated with data from this feed. Use case: Any feed which needs to be kept up to date.

_"I want to update existing entries and add new ones."_

**Delete Entries**

Delete all existing entries in this section, adding only entries from this feed. **Be careful.** Use case: Events, or when only data from the current feed is required.

_"I want only the entries from this feed in this section."_


### Using with a Cron job

Scheduling feed processing is not something thats currently built into Feed Me. Instead, you'll need to setup a Cron job, or a similar scheduled task to fire the feed processing at the desired interval. 

Find the 'Direct feed link' icon (next to the delete icon) on the main Feed Me page and copy this URL. Use one of the following to setup as a Cron Job - replacing the URL with what you just copied.

```
/usr/bin/wget -O - -q -t 1 "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"

curl --silent --compressed "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"

/usr/bin/lynx -source "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"
```

### Parameters

- `direct` _(required)_ - Must be set to `1` or `true`. Tells Feed Me this is a externally-triggered task.
- `feedId` _(required)_ - The ID of the feed you wish to process.
- `passkey` _(required)_ - A unique, generated identifier for this feed. Ensures not just anyone can trigger the import.
- `url` _(optional)_ - If your feed URL changes, you can specify it here. Ensure the structure of the feed matches your field mappings.


### Performance

Feed Me can handle importing large feeds by using Craft's Tasks service. Testing has shown that processing a feed with 10K items take roughly 15 minutes to import.

To get the most out of your feed processing, follow the below suggestions:

- Turn off `devMode`. Craft's built-in logging when `devMode` is switched on will greatly slow down the import process, and causes a high degree of memory overhead.
- Consider selecting the `Add Entries` option for duplication handling, depending on your requirements.
- Consider turning off the `Backup` option for the feed. This will depend on your specific circumstances.

You may also need to adjust the `memory_limit` and `max_execution_time` values in your php.ini file if you run into memory issues.


## Template example

While you can create a feed task to insert data as entries, there are times which you may prefer to capture feed data on-demand, rather than saving as an entry. You can easily do this through your twig templates using the below. 

Feeds are cached for performance (default to 60 seconds), which can be set by a tag parameter, or in the plugin settings.

	{% set params = {
	    url: 'http://path.to/feed/',
	    type: 'xml',
	    element: 'item',
	    cache: 60,
	} %}

	{% set feed = craft.feedme.feed(params) %}

	{% for node in feed %}
		Title: {{ node.title }}
		Publish Date: {{ node.pubDate }}
		Content: {{ node['content:encoded'] }}

		{% for name in node.category %}
			Category: {{ name }}
		{% endfor %}
	{% endfor %}

### Template parameters

- `url` _(string, required)_ - URL to the feed.
- `type` _(string, optional)_ - The type of feed you're fetching data from. Valid options are `json` or `xml` (defaults to `xml`).
- `element` _(string, optional)_ - Element to start feed from. Useful for deep feeds.
- `cache` _(bool or number, optional)_ - Whether or not to cache the request. If `true`, will use the default as set in the plugin settings, or if a number, will use that as its duration. Setting to `false` will disable cache completely.

For XML-based feeds, you will also have access to all attributes for a particular node. These are accessible through the `attributes` keyword. For example, the XML `<field my_attribute="Some Value">Another Value</field>`, you can use `{{ xml.field.attributes.my_attribute }}`.

If you're looking to consume REST feeds, APIs or other third-party platforms (Facebook, Twitter, etc), I would highly recommend using [alecritson's Placid](https://github.com/alecritson/Placid) plugin, which supports a great deal more than this plugin offers.


## Hooks

For third-party field type integration, consult the [Wiki](https://github.com/engram-design/FeedMe/wiki/Hooks).


## Roadmap

- Improve mapping by:
  - More refined and flexible mapping interface.
  - Support importing into specific locale's.
  - Allow for default value to be set.
  - Finer control over category/tag creation
  - Support uploading of assets.
  - Full support (creation) for search-only field types (Assets, Entries, Users)
  - Attribute-mapping support for XML feeds
  - Wildcard node names (for non-consistent node names)
- Allow feed processing to be reverted
- Support authentication for feed access (Basic, OAuth, Token)
- Organise documentation into Wiki
- Fix issue with mapping of Assets and the source not being set to All (not reproducible).
- Write up full tutorial from start to finish (JSON/XML).

Have a suggestion? We'd love to hear about it! [Make a suggestion](https://github.com/engram-design/FeedMe/issues)


## Release Notes

Below are major release notes when updating from one version to another. Breaking changes will be listed here.

- [Updating from 1.4.0](https://github.com/engram-design/FeedMe/wiki/Release-Notes#updating-from-140)


## Support

If you're having an issue using Feed Me, the best course of action is to send us a message through the support form on the Help tab. This will provide us with enough detail to assist.

<img width="456" src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/support.png" />

Otherwise, either [Submit an issue](https://github.com/engram-design/FeedMe/issues) or ask for assistance in the [Craft Slack Group](https://buildwithcraft.com/community#slack).


## Thanks / Contributions

A massive thanks to [Bob Olde Hampsink](https://github.com/boboldehampsink) and his amazing work on the [Import](https://github.com/boboldehampsink/import) plugin, which this plugin is clearly influenced by, and [Clearbold](https://github.com/clearbold) for [Craft Import](https://github.com/clearbold/craftimport), along with all the great users who have helped provide feedback, testing and bug reports.

[Pixel & Tonic](https://github.com/pixelandtonic) for their amazing support, assistance, and of course for creating Craft.


## Changelog

[View JSON Changelog](https://github.com/engram-design/FeedMe/blob/master/changelog.json)
