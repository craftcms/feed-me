## Changelog

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

