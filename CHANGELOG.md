## Changelog

#### 1.4.12

- Added Composer support

#### 1.4.0

- Craft 2.5 support, including release feed and icons.
- Code cleanup and refactoring field-mapping logic for performance and sanity.
- Rewritten Matrix/Table mapping and processing logic. **Matrix and Table fields will need to be re-mapped**.
- Fix for mapping multiple Matrix blocks being out of order from original feed.
- Removed Super Table native support - please ensure you have the 0.4.0 release of Super Table. **Super Table fields will need to be re-mapped**.
- Modified third-party hooks `prepForFeedMeFieldType` so it actually works! Thanks go to [@lindseydiloreto](https://github.com/lindseydiloreto).
- Added `registerFeedMeMappingOptions` for third-party fieldtypes to control the options for mapping feed nodes to field data.
- Added `postForFeedMeFieldType` for third-party fieldtypes to modify entry data before being saved to entry.
- Added documentation for hooks. Refer to [Wiki](https://github.com/engram-design/FeedMe/wiki/Hooks).
- Less strict user matching - should match against almost any value related to user.
- Allow for Environment Variables to be used in the feed url.

#### 1.3.6

- Removed `file_get_contents` as default method of fetching feed data in favour of Curl.
- Better error logging when trying to consume feed data.
- Fix for when mapping to Matrix field, commas were escaping content into new blocks.
- Ensure fields within Matrix and SuperTable are parsed through necessary field processing functions.
- Added `prepForFeedMeFieldType` hook for other plugins to handle their own fields.

#### 1.3.5

- Minor fix for logging. When Delete duplication option was set, import success was never recorded in the logs.

#### 1.3.4

- Minor fix for template mapping. Caused an issue when using a JSON feed and came across a empty nested array.

#### 1.3.3

- Minor fix for log reporting which wasn't displaying errors in a useful way.

#### 1.3.2

- Alterations to logging information to provide better feedback. Thanks to [@russback](https://github.com/russback).

#### 1.3.1

- Fix for info/notice log messages not saving when `devMode` is off.

#### 1.3.0

- Refactored direct processing to utalize Craft's tasks service, rather than using pure PHP processing. This greatly improves performance as PHP processing would run out of memory extremely quickly.

#### 1.2.9

- Added support for [SuperTable](https://github.com/engram-design/SuperTable).
- Added log tab to read in `craft/storage/runtime/logs/feedme.log`.
- Added help tab, allowing users to submit their feed info and setup for debugging/troubleshooting.
- Fix for fields in Matrix blocks only succesfully mapping textual fields. Complex fields such as Assets, Entries, etc were not mapping correctly.
- Fix for only one item being processed when Delete duplication handling was selected.
- Fix for Dropdown/RadioButtons causing a critical error when a provided value didn't exist in the field.
- Added credit and plugin url to footer.

#### 1.2.8

- Move changelog - so much change!
- Add support for attribute values for XML feeds (template tags only).
- Add missing log statement for successful update/add.

#### 1.2.7

- Fix where entries would not import if mapping element fields had more values that their field limit.
- Fix for multiple matches found on existing categories, where only one should match.
- Fix for escaping special characters in tags/category name.
- Minor fix for tags/category mapping.

#### 1.2.6

- Fix for matching fields containing special characters.
- Fix for tags and category mapping, mapping all available if supplied empty value.
- Fix for backup lightswitch reflecting the saved state.
- Fix to ensure at least one duplicate field is checked.

#### 1.2.5

- Refactoring for performance improvements.
- Remove database logging until a better performing option is figured out. Logging still occurs to the file system under `craft/storage/runtime/logs/`.
- Added optional backup option per-feed (default to true).
- Minor fix so direct feed link doesn't use `siteUrl`.

#### 1.2.4

- Added support to fallback on cURL when `file_get_contents()` is not available. Can occur on some hosts where `allow_url_fopen = 0`.

#### 1.2.3

- Primary Element is no longer required - important for JSON feeds.
- Fixes for when no primary element specified. It's pretty much optional now.
- UI tidy for mapping table.
- Fix for duplication handling not matching in some cases. Now uses element search.

#### 1.2.2

- JSON feed support.

#### 1.2.1

- Matrix support.
- Table support.
- Even better element-search.
- Remove square brackets for nested field - serialization issues. **Breaking change** you will need to re-map some fields due to this fix.
- Fix for supporting multiple entry types when selecting fields to map.

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
