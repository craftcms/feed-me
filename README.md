# Feed Me

**Version 2.0.0** - brand new and re-written from the ground up. Including features from [v1.x.x](https://github.com/engram-design/FeedMe). Currently features the following updates:

**Major features**
- Added support for Categories, Users, Entries, Commerce Products
- Support for third-party element types
- Auto-upload Assets when mapping
- Matrix smart-checking for existing content
- New parsing method for XML feeds, includes attribute-mapping
- New mapping interface allows setting defaults for most fields


**Feeds**
- Direct access to mapping screen
- Support for save shortcut, and stays on the same screen, rather than redirecting back to index
- Remove database logging (no longer used)
- Better feedback screen when unable to parse/find feed
- Fix support for local feeds
- Feed mapping now looks at entire feed structure for nodes, rather than just the first node
- Support attribute-mapping for XML feeds
- Feed no longer lags when processing from the control panel
- Proper confirmation screen when importing, with progress bar
- Fix issue where task wouldn't fire asynchronously, locking up the CP
- Added Debug controller action to help debug those tricky feeds, and see whats going on
- Fixed issue where pending/disabled existing entries weren't being matched for updating/deleting
- Support for third-party data-types, in addition to the native JSON/XML processing. Useful for custom data handling


**Fieldtypes**
- More modular handling by moving to separate classes
- More streamlined third-party integration and implementation using `registerFeedMeFieldTypes`
- Individual additional settings for some fieldtypes
-- Elements can be created if they don't exist
-- Assets can be uploaded automatically, with options for conflict handling
- Improved performance for Element fields - replaces `search` with attributes (ie: `asset->filename` over `asset->search`).
- Matrix fields now smartly look at existing content and update only if data has changed. No more element bloat.
- Added support to map element fields' own custom fields (think fields for assets). Currently only supports simple fields.


**Elements**
- Support for importing Categories, Users, Entries, Commerce Products
- Support for third-party Element Types using `registerFeedMeElementTypes`


**Events**
- Added `onBeforeProcessFeed`, `onProcessFeed`, and `onStepProcessFeed` events


**Developers**
- Added `registerFeedMeDataTypes`, `registerFeedMeElementTypes`, and `registerFeedMeFieldTypes` hooks


**Logs**
- Added ability to clear logs
- Improved logging information across the plugin


**Help**
- Fixes to Help requests not validating - therefore unable to send
- Better feedback when help requests fail
- Help request form supports CSRF protection - as it should


**Settings**
- Added ability to clear pending tasks - can be called via Cron


**Documentation**
- Dedicated plugin page via http://sgroup.com.au/plugins/feedme
- Start to finish examples
- Examples for JSON/RSS/XML feeds
- Developer resources for hooks and events, complete with example gists


Will be released as a public Beta for feedback and testing.
