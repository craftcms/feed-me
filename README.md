# Feed Me

Feed Me is a Craft plugin which makes it easy to import entries and entry data from XML, RSS or ATOM feeds. Feeds can be setup as a task in Craft's Control Panel, or called on-demand for use in your twig templates.

This is particularly useful if you wish to setup a Cron job to continually fetch data from a feed to be inserted into a section (think events, affiliate news, etc).

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

Then, select what data from the feed you wish to capture, and what fields to map it to, depending on your Section and Entry Type selection. Here you'll be able to choose which fields (can be more than one) you'd like to compare to determine if a feed item is a duplicate.

Save the feed for later, or start the import.

### Supported Fieldtypes

Feed Me supports mapping data from your feeds to the following Fieldtypes:

- Assets (existing assets only)
- Categories
- Checkboxes
- Color
- Date/Time
- Dropdown
- Entries (existing entries only)
- Lightswitch
- Multi-Select
- Number
- Plain Text
- Position Select
- Radio Buttons
- Rich Text
- Tags
- Users (existing users only)

**Assets:** Only supports mapping existing assets to this entry. Must provide filename (without extension) to successfully map. 

**Entries:** Only supports mapping existing entries to this entry. The feed field must contain either the Title or Slug of the entry to successfully map. 

**Users:** Only supports mapping existing users to this entry. The feed field must contain either the users email or username to successfully map. 

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

Scheduling feed processing is not something thats currently built into Feed Me. Instead, you'll need to setup a Cron job, or a similar scheduled task to fire the feed processing at the desired interval. A direct link can be retrieved by copying the `Run Task` link in the CP, and appending `?direct=true` to not be redirected back to the control panel.

`http://your.domain/admin/feedme/runTask/1?direct=true`

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

- Support mapping for all common Craft Fieldtypes (Matrix, Table)
- Full support (creation) for search-only Fieldtypes (Assets, Entries, Users)
- Support third-party Fieldtypes through hook
- Finer control over category/tag creation
- Improved logging of import process for individual feed items
- Allow feed processing to be reverted
- Allow static values to be set for fields when mapping
- Support feeds with content more than 2 levels from the start node
- JSON feed support
- Batch processing for very long feeds
- Support authentication for feed access (Basic, OAuth, Token)

Have a suggestion? We'd love to hear about it! [Make a suggestion](https://github.com/engram-design/FeedMe/issues)

## Bugs, feature requests, support

Found a bug? Have a suggestion? [Submit an issue](https://github.com/engram-design/FeedMe/issues)

## Thanks / Contributions

A massive thanks to [Bob Olde Hampsink](https://github.com/boboldehampsink) and his amazing work on the [Import](https://github.com/boboldehampsink/import) plugin, which this plugin is clearly influenced by, and [Clearbold](https://github.com/clearbold) for [Craft Import](https://github.com/clearbold/craftimport).

[Pixel & Tonic](https://github.com/pixelandtonic) for their amazing support, assistance, and of course for creating Craft.
