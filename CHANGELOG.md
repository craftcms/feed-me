# Changelog

## 3.0.0-beta.9 - 2018-04-28

### Fixed

- Fix errors for help form causing zip file not to send
- Fix additional options not working for Matrix fields

## 3.0.0-beta.8 - 2018-04-27

### Fixed

- Missing version number (sigh)

## 3.0.0-beta.7 - 2018-04-27

### Fixed

- Add better support for Matrix fields in Help requests
- Add conditional around entry field section/types

## 3.0.0-beta.6 - 2018-04-24

### Changed

- Tighten restrictions on allowed sub-element fields

### Fixed

- Fix complex data not respecting `usedefault`
- Matrix - fix for complex fields not processing just their subset of data.
- Fix a few missing translation namespaces
- Fix processing of `{` in content, where Feed Me thinks this is a Twig short tag
- Fix missing collapsed option for Matrix fields
- Fix entries section/type not being hidden initially

## 3.0.0-beta.5 - 2018-04-22

### Added

- Added support for `yyyymmdd` and `yyyyddmm` date formats
- Added options to select section/type for entry fields when creating new entries

### Fixed

- Fix user status not being set for users
- Fix incorrect author being set on entries
- Fix error thrown by fields that have `default` attributes
- Fixed sub-element sources throwing errors

## 3.0.0-beta.4 - 2018-04-11

### Fixed

- Revert update `league/csv` dependancy

## 3.0.0-beta.3 - 2018-04-10

### Fixed

- Fix database column type for backup option causing issues
- Update `league/csv` dependancy
- Prevent element fields matching all existing elements when not provided a value
- Fix compatibility with Craft Client/Solo
- Check before trying to split pipe characters in values
- Fix backup option being switched off

## 3.0.0-beta.2 - 2018-04-04

### Fixed

- Fix leftover `fieldElementMapping` references


## 3.0.0-beta.1 - 2018-04-03

> {warning} Feed Me 3.0.0 is a major release with significant, breaking changes. Be sure to back up your existing Feed Me 2.x.x settings. In most cases, you'll be required to re-map fields to your feed data, as this has been heavily changed and improved.

### Added
- Support for Craft 3, and all that comes with it.
- Support for CSV importing.
- New settings pane for each feed (on the Feeds page) stores useful links for Debug, Duplicate and Direct Feed URL.
- Primary Element setup is now much easier/clearer with preview of available nodes, rather than a text input. Should help with understanding this critical setting. Full preview coming in v3.1
- Unit tests for parsing logic, feed consumption and more.
- Added more hooks for feed fetching, element parsing, field parsing, and processing.
- Added `limit` and `offset` parameters when running a feed via the Direct Feed URL or Debug.
- Added mapping option for selecting to always use the default value, even when not mapping the field. Handy for mass populating data without feed values.
- Added support for pipe `|` character to separate multiple field contents for element fields. Useful when Categories for instance are supplied as `Category 1|Category 2`. This is also the only method of mapping multiple items to a single field when using CSV.

### Changed
- Completely re-written parsing logic for feed data, supported by unit tests. This is boring to most, but exciting for the stability of the plugin :)
- Improved handling of attribute mapping, now more options for things like dates. Now acts and uses the same logic for fields.
- Better handling of default field values under the hood.
- Lots of smaller improvements pathing the way to more major changes in 3.1.0.
- Better support for uploading remote assets (a little faster too).
- When running from the command line (curl, wget, etc), feed processing will wait until it's finished before ending the request.
- Improved UI for mapping complex fields (Matrix, Table). Includes setting collapsed state for Matrix fields.
- Improved UI for mapping sub-element fields. Fields are hidden by default and can be toggled to provide better visual clarity.
- Improved UI for logs, also shows log 'type' to easily pick up errors.
- When a feed fails or contains errors it will no longer show the red error symbol for the queue job. Errors are recorded in the logs, but it won't cause other queue jobs to prevent being run, and notifying clients of an error.
- Logs now capture additional information of line/file when exceptions occur.
- utilise Guzzle for all HTTP requests, rather than new instances of Curl.
- Improved Help widget to utilise API for sending emails, not relying on local relay. Caused immeasurable amount of issues for people try to get support!

## 2.0.11 - 2017-12-14

### Fixed
- Fixed incorrectly setting `localeEnabled` behaviour for entries.
- Fixed issue related to backups, causing feeds to fail when triggered via the control panel.

## 2.0.10 - 2017-12-13

### Added
- Add `backupLimit` config option to control the number of backups to retain (when backups are in use).
- Products can now have their sub-element fields mapped to.

### Changed
- Improve response when triggered via cron to not return the full page response.
- Better support for timestamps provided with milliseconds.

### Fixed
- Fix a bug that occured when disable/delete elements is your import strategy.
- Update entries to only apply localeEnabled when dealing with locales.
- Fix for CP becoming unresponsive when running a feed from the control panel directly.
- Fix to ensure local-testing works as expected without a license.
- Fix for Commerce Products default values not being properly sorted into variant data.
- Fixed an issue when triggering feeds from CLI. Thanks to [@joshangell](https://github.com/verbb/feed-me/pull/262).
- Fix for date attributes not checking for falsey values before returning current date.
- Fix for locale entries not having their status set as per the default section status.

## 2.0.9 - 2017-10-18

### Fixed
- Fixed icon mask.
- Fixed incorrect license servers for new licenses.

## 2.0.8 - 2017-10-17

### Added
- Verbb marketing (new plugin icon, readme, etc).
- Add headers from feed request to special `_headers` variable for template requests
- Create `feedHeaders` template call
- Add commerce products fieldtype support

### Changed
- Better handle boolean values for elements.
- Refactor status screen.
- Users - allow importing into multiple user groups.
- Users - support remote-uploading of profile image.
- Visual improvements to nested element fields.
- Matrix - Allow block enabled status to be set.
- Reset properties to allow an instance of the ProcessService to be reused..
- Support additional boolean values for fields and attributes.
- Date - Add date formatting options for date field.
- Ensure each run of the feed uses a fresh criteria model.
- Matrix - improvements for Super Table handling.
- Add extra truthy detections for ‘live’ and ‘enabled’.

### Fixed
- Load custom (fixed) version of selectize to fix being unable to un-select defaults.
- Matrix - fix default value to not be enabled (don’t map).
- Table - properly normalise data.
- Users - minor fix for groupId checking.
- Fix feed not getting immediately processed when running from the CP.
- Fix minor issue with categories calling ids() twice.
- Fix regex for short-hand twig detection.
- Fix for Table field not processing more than 10 rows.
- Ensure more than just plain sub-element field are styed correctly.
- Elements - Ensure we properly escape ‘{‘ characters, and don’t assume they’re short-hand twig.
- Entries fieldtype - don’t rely on parent element being a section.
- Assets - Fix folder-mapping from 2.0.7 (requires re-mapping).
- Support for limitAutoSlugsToAscii for element slugs.
- Remove Ajax task-triggering from direct-run template. In cases, this can cause the feed to be run twice, running from Cron.
- Commerce - using existing variant Title when not provided.
- Honour default entry status for sections.
- Slight modification to regex processing.
- Fix incorrect function calls for help request and certain fields.
- Commerce - Tax and Shipping category now supports ID, Name and Handle.
- Fix bug that causes datetimes not to be adjusted for timezones on import.
- Clean field-mapping options and fields when saving, and the field isn’t being mapped.
- Check Matrix field data if it has any field content - otherwise will create empty blocks.
- Allow complex fields to have their defaults set correctly.

## 2.0.7 - 2017-06-22

### Added
- Assets - Added folder option for dynamic sub-folders (via feed), or by selecting specific default folders within selected source.
- Add default field for element IDs
- Allow elements to use date attributes as unique identifier.
- Add backup logging.
- Add duplicate feed option, and move into first screen.
- New Status screen - start feed processing from here, or come back to view the process of a running one.
- Add `limit` and `offset` params to debug.

### Changed
- Use selectize for mapping dropdowns. Allows for quick-searching of data.
- Ensure elements can handle Twig shortcode.
- Link to logs/home on import success page.
- Better text on asset-uploading options fields - hopefully explains better.

### Fixed
- Products - Fix single variant handling.
- Fix a typo for `Disableed`.
- Fix for Table field in Matrix.
- Fix potential SQL error when setting entries to disabled. Thanks @lindseydiloreto.
- Products - Fix single variant handling.
- Ensure inner-element fields check for existing content.
- Fix date fields not parsing twig variables early.
- Minor fix for asset importing not expecting array of urls.
- Fix to check empty string passed in as date.
- Minor fixes for `defaultUploadLocationSource` checks.
- Improve Matrix-handling when referencing data outside of repeatable Matrix-ready content (you’re correct that sounds complex).
- Fix related entries escaping title.
- Fix regex to handle node names that end in digit. Thanks @antcooper.
- Minor fix for Table fields and normalising data.
- Fix Commerce Variants not calling `postForFieldType`.

## 2.0.6 - 2017-05-19

### Added
- Allow option-select fields (Dropdown, Checkboxes, etc) to specify whether data is provided as its value or label

### Changed
- Prevent against specifying incorrect handles for fields or attributes for default field values (using twig)
- Check categories (and other fields) have correct settings before fetching custom fields
- Ensure required fields get their data populated when targeting a specific locale. Otherwise, will cause the feed to fail due to validation.

### Fixed
- Users - fixed replacing current user group assignments
- Minor fix for complex/uneven Matrix fields
- Fix to check for existing parsed data and merge, rather than overwrite
- Fix Product/Variant imports not working correctly

## 2.0.5 - 2017-05-12

### Added
- Added `checkExistingFieldData` for all fields to first check an existing element (if any) has content matching exactly what is to be imported. It will be skipped if it matches. Controlled by config setting, and off by default.

### Changed
- Provide better checking for set DateTime object for all date fields.
- Better support for null data in feed.
- Refactoring feed data parsing (for the last time!) for more reliable results with inconsistent feeds/nodes/data. Much better handling of order of data, nested content, and repeatable nodes.

### Fixed
- Fix asset inner-element fields when remote-uploading.
- Fix asset `postFieldData` fetching incorrect field data.
- Fix asset filename guessing to handle more robust filenames.
- Remove unnecessary log when testing for timestamp.

## 2.0.4 - 2017-05-03

### Added
- Allow Twig shorthand variables to be used in default fields. This allows you to reference other fields or element attributes in the feed.
- Add `nesbot/carbon` for proper date parsing. Now properly supports Unix timestamps for date data.
- Smart extension/filename checking for remote asset URLs. This smartly grabs the correct URL from the base path, or query string.

### Changed
- Commerce Variants - pre-fill existing attribute values if they aren’t mapped in the feed.
- Include more default fields for element types.

### Fixed
- Fixed issue with setting Category status.
- Fixed data issue in `BaseFeedMeFieldType:postFieldData()`.

## 2.0.3 - 2017-04-30

### Added
- Added Locale Status to entries, to properly control individual locale status.
- Added `element` parameter to `onStepProcessFeed` event.
- Added Import Strategy to `Disable missing elements`. Thanks [@ryansnowden](https://github.com/ryansnowden).

### Changed
- Refactor remote asset uploading/handling.
- Remote assets - Better support for dynamic paths set in asset fields (ie `{slug}`).
- Remote assets - When set to `Keep Original`, don't download it and then check if it exists in Craft - it can be skipped.
- Ensure all fields are bootstrapped with the owner element being imported.
- Improve Commerce Products matching on existing elements (including better variant-field support).
- Remove certain unique identifier options for Product Variants - the element criteria doesn’t support them anyway.
- Refactor `matchExistingElement` to return a single element, as opposed to a collection.
- Improve checking for unique identifier for all element types.

### Fixed
- Remote assets - Ensure field limits are respected.
- Remote assets - Ensure multi remote asset fields work correctly in the same feed.
- Fix for entry field not showing correct sub-element fields.
- Fix for Matrix and remote asset field uploading.

## 2.0.2 - 2017-03-29

### Added
- Provide `all` locale option instead of always requiring one.

### Changed
- Refactored Asset element type importing, include locale content.
- Better support for Craft Client default author for elements.
- Empty date/time can now be set for date fields.
- Improved Checkbox/Radio/Multiselect data parsing.
- Plugin events are now triggered when calling the debug action.
- Long URLs are now truncated on feeds index in the CP.
- Improved assets-uploading without extension.

### Fixed
- Properly support third-party element types.
- Properly support third-party data types (thanks to [@timeverts](https://github.com/verbb/feed-me/pull/172)).
- Fixed default value for dropdown field (when no value matches).
- Better error-handling when importing into specific locale.
- Ensure default start/date are correctly parsed entries/commerce.
- Ensure checks for element attributes include fallback. Can cause issues with fields with handles the same as element attributes.
- Lightswitch field now properly check for boolean values.
- Fixed User Photos not being mapped.
- Fixed `preferredLocale` not being mapped to users.
- Fixed logging license response on ping. This caused an error to be raised during feed processing at times.
- Fixed for Asset Element not matching against filename data.
- Fixes for Matrix/Table importing.

## 2.0.1 - 2017-02-28

### Fixed
- Products - Fix existing element-matching on core attributes (matching against title, etc)
- Products - Ensure content is set correctly for multi-locales
- Users - Fix profile fields not importing when multi-locales were setup.
- Fix data-parsing when using via templates. Particular if an error occurs, an error would be thrown, preventing logging of the error.
- Ensure `unregisterLicenseKey` and `transferLicenseKey` properly decode server response.

## 2.0.0 - 2017-02-24

### Feed Me 2.0 is a major update
- Before updating, please read the [upgrade notes](https://sgroup.com.au/plugins/feedme/guides/updating-from-1-x-x). This version contains several potentially breaking changes.

### Added
- Added support for Categories, Users, Entries, Commerce Products
- Support for third-party element types
- Auto-upload Assets when mapping
- Support to map content to element's inner fields (think fields for assets)
- Added Assets ElementType - easily upload assets into Craft.
- Direct access to mapping screen
- Support attribute-mapping for XML feeds
- Added Debug controller action to help debug those tricky feeds, and see whats going on
- Support for third-party data-types, in addition to the native JSON/XML processing. Useful for custom data handling
- Added debug icon to Feeds index.
- Added Element ID mapping. *A word of warning - * do not use this for importing new data. [Read more](https://sgroup.com.au/plugins/feedme/feature-tour/field-mapping#element-iDs).
- Added parent-mapping for Entry and Category.
- Elements can be created if they don't exist
- Assets can be uploaded automatically, with options for conflict handling
- Added support to map element fields' own custom fields (think fields for assets). Currently only supports simple fields.
- Support for importing Categories, Users, Entries, Commerce Products
- Support for third-party Element Types using `registerFeedMeElementTypes`
- Added `onBeforeProcessFeed`, `onProcessFeed`, and `onStepProcessFeed` events
- Added `registerFeedMeDataTypes`, `registerFeedMeElementTypes`, and `registerFeedMeFieldTypes` hooks
- Added ability to clear logs
- Added ability to clear pending tasks - can be called via Cron
- Dedicated plugin page via [http://sgroup.com.au/plugins/feedme](http://sgroup.com.au/plugins/feedme)
- Start to finish examples
- Examples for JSON/RSS/XML feeds
- Developer resources for hooks and events

### Changed
- Matrix smart-checking for existing content
- New parsing method for XML feeds, includes attribute-mapping
- New mapping interface allows setting defaults for most fields
- Re-written field-mapping functionality (again) to be more robust and future-proof.
- Import Strategy is now no longer a single choice. Choose any combination of Add/Update or Delete.
- Locale-specific importing. Previously imported the same content across locales.
- Support for save shortcut, and stays on the same screen, rather than redirecting back to index
- Better feedback screen when unable to parse/find feed
- Feed mapping now looks at entire feed structure for nodes, rather than just the first node
- Feed mapping is no longer case-insensitive
- Proper confirmation screen when importing, with progress bar
- Feeds no longer die when an error occurs. It'll try to complete the rest of the feed, notifying you of errors at the end of processing.
- Sub-folders are now searched for existing assets.
- Improved handling of inconsistent, repeatable nodes (I'm looking at you XML).
- Asterisks are now shown during mapping for required fields - a handy reminder.
- User importing no longer requires a User Group set.
- More modular handling by moving to separate classes
- More streamlined third-party integration and implementation using `registerFeedMeFieldTypes`
- Improved performance for Element fields - replaces `search` with attributes (ie: `asset->filename` over `asset->search`).
- Matrix fields now smartly look at existing content and update only if data has changed. No more element bloat.
- Improved logging information across the plugin
- Better feedback when help requests fail

### Fixed
- Remove database logging (no longer used)
- Fix support for local feeds
- Feed no longer lags when processing from the control panel
- Fix issue where task wouldn't fire asynchronously, locking up the CP
- Fixed issue where pending/disabled existing entries weren't being matched for updating/deleting
- Prevent feed from processing if there are no nodes to process. Fixes deletion when elements shouldn't be
- Treat boolean-like values with the respect they deserve.
- Added Shipping Category for Commerce Products.
- Fixes to Help requests not validating - therefore unable to send
- Help request form supports CSRF protection - as it should

## 1.4.12 - 2016-07-05

### Changed
- Altered terminology around Duplication Handling to hopefully be more clearer.

### Fixed
- Protect against errors on Feed Me index page when sections/entry types no longer exist.
- Fixed mapping issues with Table field inside Matrix blocks.
- Fixed Dropdown field matching via Label, not Value.

## 1.4.11 - 2016-04-25

### Fixed
- Fixed issue with XML parsing and special characters encoding incorrectly.

## 1.4.10 - 2016-04-13

### Fixed
- Fixed issue for repeatable fields containing empty values (table fields).

### Changed
- FeedUrl attribute stored as `TEXT` column type to allow for longer URLs.
- Improved JSON parsing to use Yii's JsonHelper class, with better logging when failing.

## 1.4.9 - 2016-03-15

### Fixed
- Fixed issue with utf8 encoding for feeds.

### Changed
- Improvements to matrix processing.

## 1.4.8 - 2016-03-02

### Added
- Fix for json parsing when special characters in feed content.

### Changed
- Better logging when a feed cannot be parsed.

## 1.4.7 - 2016-02-28

### Added
- Added support for locales - set which locale you want your feed to go to.
- Added support for non-http protocols for feeds (ftp://, file://, etc) [#29](https://github.com/verbb/feed-me/issues/29)

## 1.4.6 - 2016-01-19

### Fixed
- Fixed an issue where an error would be incorrectly thrown when Add duplication handling is used.

## 1.4.5 - 2016-01-13

### Fixed
- Fixed issue with plugin release feed url.

## 1.4.4 - 2015-12-27

### Fixed
- Fixed issue with irregular nested elements. See [#24](https://github.com/verbb/feed-me/issues/24#issuecomment-167106972).

## 1.4.3 - 2015-12-01

### Fixed
- Check for both numeric and string single-string arrays. Particularly an issue for JSON feeds.

## 1.4.2 - 2015-11-27

### Changed
- Minor improvements for plugin icons.

## 1.4.1 - 2015-11-26

### Fixed
- Fix css/js resources filename, which did not commit properly.

## 1.4.0 - 2015-11-25

### Added
- Craft 2.5 support, including release feed and icons.
- Added `registerFeedMeMappingOptions` for third-party fieldtypes to control the options for mapping feed nodes to field data.
- Added `postForFeedMeFieldType` for third-party fieldtypes to modify entry data before being saved to entry.
- Added documentation for hooks. Refer to [Wiki](https://github.com/verbb/feed-me/wiki/Hooks).

### Changed
- Code cleanup and refactoring field-mapping logic for performance and sanity.
- Rewritten Matrix/Table mapping and processing logic. **Matrix and Table fields will need to be re-mapped**.
- Removed Super Table native support - please ensure you have the 0.3.9 release of Super Table. **Super Table fields will need to be re-mapped**.
- Less strict user matching - should match against almost any value related to user.
- Allow for Environment Variables to be used in the feed url.
- Better feedback on feed failure. Will fail task if _any_ feed node encounters an issue, and will show the red failed task indicator (prompting you to look at the logs).
- Modified third-party hooks `prepForFeedMeFieldType` so it actually works! Thanks go to [@lindseydiloreto](https://github.com/lindseydiloreto).

### Fixed
- Fix for mapping multiple Matrix blocks being out of order from original feed.
- Fix issue with task not firing when running from Control Panel. In some cases, this meant not even logging information was being recorded if something went wrong. This did not effect running directly.

## 1.3.6 - 2015-11-25

- Removed `file_get_contents` as default method of fetching feed data in favour of Curl.
- Better error logging when trying to consume feed data.
- Fix for when mapping to Matrix field, commas were escaping content into new blocks.
- Ensure fields within Matrix and SuperTable are parsed through necessary field processing functions.
- Added `prepForFeedMeFieldType` hook for other plugins to handle their own fields.

## 1.3.5 - 2015-11-25

- Minor fix for logging. When Delete duplication option was set, import success was never recorded in the logs.

## 1.3.4 - 2015-11-25

- Minor fix for template mapping. Caused an issue when using a JSON feed and came across a empty nested array.

## 1.3.3 - 2015-11-25

- Minor fix for log reporting which wasn't displaying errors in a useful way.

## 1.3.2 - 2015-11-25

- Alterations to logging information to provide better feedback. Thanks to [@russback](https://github.com/russback).

## 1.3.1 - 2015-11-25

- Fix for info/notice log messages not saving when `devMode` is off.

## 1.3.0 - 2015-11-25

- Refactored direct processing to utalize Craft's tasks service, rather than using pure PHP processing. This greatly improves performance as PHP processing would run out of memory extremely quickly.

## 1.2.9 - 2015-11-25

- Added support for [SuperTable](https://github.com/engram-design/SuperTable).
- Added log tab to read in `craft/storage/runtime/logs/feedme.log`.
- Added help tab, allowing users to submit their feed info and setup for debugging/troubleshooting.
- Fix for fields in Matrix blocks only succesfully mapping textual fields. Complex fields such as Assets, Entries, etc were not mapping correctly.
- Fix for only one item being processed when Delete duplication handling was selected.
- Fix for Dropdown/RadioButtons causing a critical error when a provided value didn't exist in the field.
- Added credit and plugin url to footer.

## 1.2.8 - 2015-11-25

- Move changelog - so much change!
- Add support for attribute values for XML feeds (template tags only).
- Add missing log statement for successful update/add.

## 1.2.7 - 2015-11-25

- Fix where entries would not import if mapping element fields had more values that their field limit.
- Fix for multiple matches found on existing categories, where only one should match.
- Fix for escaping special characters in tags/category name.
- Minor fix for tags/category mapping.

## 1.2.6 - 2015-11-25

- Fix for matching fields containing special characters.
- Fix for tags and category mapping, mapping all available if supplied empty value.
- Fix for backup lightswitch reflecting the saved state.
- Fix to ensure at least one duplicate field is checked.

## 1.2.5 - 2015-11-25

- Refactoring for performance improvements.
- Remove database logging until a better performing option is figured out. Logging still occurs to the file system under `craft/storage/runtime/logs/`.
- Added optional backup option per-feed (default to true).
- Minor fix so direct feed link doesn't use `siteUrl`.

## 1.2.4 - 2015-11-25

- Added support to fallback on cURL when `file_get_contents()` is not available. Can occur on some hosts where `allow_url_fopen = 0`.

## 1.2.3 - 2015-11-25

- Primary Element is no longer required - important for JSON feeds.
- Fixes for when no primary element specified. It's pretty much optional now.
- UI tidy for mapping table.
- Fix for duplication handling not matching in some cases. Now uses element search.

## 1.2.2 - 2015-11-25

- JSON feed support.

## 1.2.1 - 2015-11-25

- Matrix support.
- Table support.
- Even better element-search.
- Remove square brackets for nested field - serialization issues. **Breaking change** you will need to re-map some fields due to this fix.
- Fix for supporting multiple entry types when selecting fields to map.

## 1.2 - 2015-11-25

- Lots of fixes and improvements for direct-processing. Includes URL parameter, passkey and non-Task processing.
- Fixes with logging - now more informative!
- Improvement nested element parsing.
- Better date parsing.
- CSRF protection compatibility.
- Fix for duplicate field mapping not being remembered.

## 1.1 - 2015-11-25

- Prep for Table/Matrix mapping.
- Better depth-mapping for feed data (was limited to depth-2).
- Refactor field-mapping processing.
- Set minimum Craft build.

## 1.0 - 2015-11-25

- Initial release.
