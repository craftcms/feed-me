# Feed Me

Feed Me is a Craft plugin to make it easy to import entries and entry data from XML, RSS or ATOM feeds. Feeds can be setup as a task in Craft's Control Panel, or called on-demand for use in your twig templates.

A common use-case for this plugin is to consume external feeds (news, events), but can also be used as a once-off task for importing content when migrating from other sites.

<img src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/main.png" />


## Features

- Import data from XML, RSS or ATOM feeds.
- Feeds are saved to allow easy re-processing on-demand, or to be used in a Cron job.
- Map feed data to your entry fields. See Supported Fieldtypes.
- Duplication handling - control what happens when feeds are processed again.
- Uses Craft's Task service to process feeds in the background.
- Database backups before each feed processing.
- Troubleshoot feed processing issues with logs.
- Grab feed data directly from your twig templates.


## Install

- Add the `feedme` directory into your `craft/plugins` directory.
- Navigate to Settings -> Plugins and click the "Install" button.

**Plugin options**

- Change the plugin name as it appear in the CP navigation.
- Set the default cache (for calls using the template tag only).


## Usage

Head to the Feed Me page using the CP navigation, and click the New Feed button.

Enter the required details to configure your feed:

- Name this feed something useful so you'll remember what it does.
- The actual URL to your feed.
- Set the Feed Type to assist with mapping the correct elements.
- The Primary XML Element reflects which node in the feed your data sits.
- Select the Section and Entry Type for where you want to put the feed data.
- Decide how you'd like to handle duplicate feed items (if you're going to be re-running this feed).

<img src="https://raw.githubusercontent.com/engram-design/FeedMe/master/screenshots/mapping_1.png" />

Then, select what data from the feed you wish to capture, and what fields to map it to, depending on your Section and Entry Type selection. Here you'll be able to choose which fields (can be more than one) you'd like to compare to determine if a feed item is a duplicate.

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
- Multi-Select
- Number
- Plain Text
- Position Select
- Radio Buttons
- Rich Text
- Tags
- Users

**Planned support**

- Matrix
- Table


###Element Creation

For certain elements, it may be benefitial to create the element's data, if not already created. Like if an Asset doesn't exist in your Assets collection, upload it. Similarly with Categories, and other fields.

Currently, Feed Me handles the following applicable fields in these ways:

**Assets:** Only supports mapping existing assets to this entry. Must provide filename (without extension) to successfully map.

**Categories:** Are created if they do not exist, or mapped if they do. The value provided in the feed data must be the Name for the category.

**Entries:** Only supports mapping existing entries to this entry. The feed field must contain either the Title or Slug of the entry to successfully map. 

**Tags:** Are always created.

**Users:** Only supports mapping existing users to this entry. The feed field must contain either the users email or username to successfully map. 

We plan to include options for whether you would like to do this on a per-field basis.


### Duplication Handling

When running the feed task multiple times, there may or may not be the same data present in the feed as the last time you ran the task. To deal with this, you can control what happens to old (and new) entries when the feed is processed again.

You may choose multiple fields to determine if an entry is a duplicate. Most commonly, you'll want to compare the `Title` field, but can be any fields you require.

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

Scheduling feed processing is not something thats currently built into Feed Me. Instead, you'll need to setup a Cron job, or a similar scheduled task to fire the feed processing at the desired interval. Right-click on the 'Direct feed link' icon (next to the delete icon) on the main Feed Me page and select 'Copy Link Address'. Use one of the following to setup as a Cron Job - replacing the URL with what you just copied.

```
/usr/bin/wget -O - -q -t 1 "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"

curl --silent --compressed "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"

/usr/bin/lynx -source "http://your.domain/actions/feedMe/feeds/runTask?direct=1&feedId=1&passkey=FwafY5kg3c"
```

### Parameters

- `direct` _(required)_ - Must be set to `1` or `true`. Tells Feed Me this is a externally-triggered task.
- `feedId` _(required)_ - The ID of the feed item you wish to process.
- `passkey` _(required)_ - A unqiue, generated identifier for this feed. Ensures not just anyone can trigger the import.
- `url` _(optional)_ - If your feed URL changes, you can specify it here. Ensure the structure of the feed matches your field mappings.


## Template example

While you can create a feed task to insert data as entries, there are times which you may prefer to capture feed data on-demand, rather than saving as an entry. You can easily do this through your twig templates using the below. 

Feeds are cached for performance (default to 60 seconds), which can be set by a tag parameter, or in the plugin settings.

	{% set params = {
	    url: 'http://path.to/feed/',
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

- `url` _(string)_ - URL to the feed. Required
- `element` _(string)_ - Element to start feed from. Useful for deep feeds. Optional
- `cache` _(bool or number)_ - Whether or not to cache the request. If true, will use the default as set in the plugin settings, or if a number, will use that as its duration. Optional

If you're looking to consume REST feeds, APIs or other third-party platforms (Facebook, Twitter, etc), I would highly recommend using [alecritson's Placid](https://github.com/alecritson/Placid) plugin, which supports a great deal more than this plugin offers.


## Roadmap

- Support mapping for all common Craft field types (Matrix, Table)
- Full support (creation) for search-only field types (Assets, Entries, Users)
- Support third-party field types through hook
- Finer control over category/tag creation
- Improved logging of import process for individual feed items
- Allow feed processing to be reverted
- Allow static/default values to be set for fields when mapping
- JSON feed support
- Batch processing for very long feeds
- Support authentication for feed access (Basic, OAuth, Token)
- Support for locale's
- Organise documentation into Wiki

Have a suggestion? We'd love to hear about it! [Make a suggestion](https://github.com/engram-design/FeedMe/issues)


## Bugs, feature requests, support

Found a bug? Have a suggestion? [Submit an issue](https://github.com/engram-design/FeedMe/issues)

For support, either [Submit an issue](https://github.com/engram-design/FeedMe/issues) or ask for assistance in the [Craft Slack Group](https://buildwithcraft.com/community#slack).


## Thanks / Contributions

A massive thanks to [Bob Olde Hampsink](https://github.com/boboldehampsink) and his amazing work on the [Import](https://github.com/boboldehampsink/import) plugin, which this plugin is clearly influenced by, and [Clearbold](https://github.com/clearbold) for [Craft Import](https://github.com/clearbold/craftimport).

[Pixel & Tonic](https://github.com/pixelandtonic) for their amazing support, assistance, and of course for creating Craft.


## Changelog

#### 1.2

- Lots of fixes and improvements for direct-processing. Includes URL parameter, passkey and non-Task processing.
- Fixes with logging - now more informative!
- Improvement nested element parsing.
- Better date parsing.
- CSRF protection compatibility.
- Fix for duplicate field mapping not being remembered.


#### 1.1

- Prep for Table/Matrix mapping.
- Better depth-mapping for feed data (was limited to depth-2).
- Refactor field-mapping processing.
- Set minimum Craft build.

#### 1.0

- Initial release.

