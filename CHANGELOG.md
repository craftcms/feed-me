# Changelog

## 4.4.1 - 2021-12-03

### Changed
- The `feedId` is now passed as a parameter into the `beforeFetchFeed` and `afterFetchFeed` events.
- `craft\services\Process::processFeed()` now accepts a `$feedData` parameter that can be used to override the feed data.

### Fixed 
- Fixed a bug where mapped values could get stripped of leading zeros when doing content comparisons.
- Fixed a bug where creating or editing a feed would render an error if you were on Craft 3.7.24 or later. ([#1065](https://github.com/craftcms/feed-me/issues/1065))

## 4.4.0 - 2021-08-08

### Added
- Added support for disabling updating search indexing on a per-feed basis. ([#649](https://github.com/craftcms/feed-me/issues/649))

### Changed
- Feed Me now requires Craft 3.7 or later.

### Fixed
- Fixed a bug that could occur when matching content on Entries, Categories and Users fields. ([#864](https://github.com/craftcms/feed-me/issues/864))
- Fixed a bug that could occur if the feed value wasn’t a `string` when importing into a dropdown field. ([#853](https://github.com/craftcms/feed-me/issues/853))
- Fixed a PHP error that could occur when trying to import a remote asset.
- Fixed a bug where Feed Me was not respecting Craft’s `allowUppercaseInSlug` config setting. ([#853](https://github.com/craftcms/feed-me/issues/865))

## 4.3.6 - 2021-03-05

### Added
- Added support for the [Google Maps](https://plugins.craftcms.com/google-maps) plugin.
- Added a `--all` option to the `feed-me/feeds/queue` console command to push all feeds into the queue. ([#783](https://github.com/craftcms/feed-me/pull/783))

### Changed
- Feed labels are now sorted alphabetically on the feed’s field mapping settings page. ([#812](https://github.com/craftcms/feed-me/pull/812))

### Fixed
- Fixed a bug where Solspace Calendar events would not import when using some recurring rules. ([#806](https://github.com/craftcms/feed-me/pull/806))
- Fixed a bug where using the `feedOptions` config setting could unintentionally overwrite the wrong feed’s settings. ([#792](https://github.com/craftcms/feed-me/issues/792))

## 4.3.5.1 - 2021-02-07

### Fixed
- Fixed a PHP error that would occur when importing Assets. ([#804](https://github.com/craftcms/feed-me/issues/804))

## 4.3.5 - 2021-02-05

### Removed
- Removed the ralouphie/mimey library.

### Fixed
- Fixed a bug where CSV files without a header row weren’t getting loaded properly when using league/csv 9.x. ([#798](https://github.com/craftcms/feed-me/issues/798))
- Fixed some PHP 8 compatibility issues. ([#802](https://github.com/craftcms/feed-me/issues/802))

## 4.3.4 - 2020-12-14

### Fixed
- Fixed a bug that prevented some feeds from importing. ([#786](https://github.com/craftcms/feed-me/issues/786))
- Fixed a PHP error that would occur when importing into a custom field named `variants`. ([#616](https://github.com/craftcms/feed-me/issues/616))

## 4.3.3 - 2020-12-10

### Fixed
- Fixed a PHP error that occurred when importing a JSON object, rather than an array (again). ([#761](https://github.com/craftcms/feed-me/issues/761))
- Fixed a MySQL error that could occur when importing values of zero.  ([#779](https://github.com/craftcms/feed-me/issues/779))
- Fixed a bug where local filesystem feeds would not run on Windows. ([#655](https://github.com/craftcms/feed-me/issues/655))

## 4.3.2 - 2020-11-15

### Fixed
- Fixed a PHP error that could occur when importing assets. ([#355](https://github.com/craftcms/feed-me/issues/355)), ([#747](https://github.com/craftcms/feed-me/issues/747))

## 4.3.1 - 2020-11-09

### Fixed
- Fixed a bug where feeds’ import strategy settings would get reset to the default values when editing existing feeds. ([#769](https://github.com/craftcms/feed-me/issues/769))
- Fixed a bug where assets with uppercase file extensions wouldn’t import on case-sensitive file systems. ([#691](https://github.com/craftcms/feed-me/issues/691))
- Fixed a bug that broke asset importing in 4.3.0.

## 4.3.0 - 2020-11-06

### Added
- It’s now possible to import Single sections’ entry data. ([#559](https://github.com/craftcms/feed-me/issues/559))
- It’s now possible to import global set data. ([#670](https://github.com/craftcms/feed-me/issues/670))
- Added the `feed-me/feeds/queue` command. ([#754](https://github.com/craftcms/feed-me/issues/754), [#626](https://github.com/craftcms/feed-me/issues/626))
- Added `craft\feedme\elements\GlobalSet`.
- Added `craft\feedme\models\ElementGroup`.
- Added `craft\feedme\models\FeedModel::$singleton`.

### Changed
- Feed Me now requires Craft 3.5 or later.
- Cleaned up log messages when there are no items in a feed to process. ([#585](https://github.com/craftcms/feed-me/issues/585))
- Elements’ `getGroups()` methods can now return `craft\feedme\models\ElementGroup` objects.
- Updated ralouphie/mimey to v2.1. ([#740](https://github.com/craftcms/feed-me/issues/740))

### Removed
- Removed the `feed-me/feeds/run` command. The new `feed-me/feeds/queue` command can be used instead, in combination with `queue/run`.

### Fixed
- Fixed a bug where it was possible to configure feeds with validation issues. ([#757](https://github.com/craftcms/feed-me/issues/757))
- Fixed a SQL error that occurred when importing products in Commerce 3.2.8 or later.
- Fixed a bug where assets would end up with an incorrect filename when the filename was used for a mapping in the feed settings. ([#750](https://github.com/craftcms/feed-me/issues/750))
- Fixed a bug where custom fields would have their content assigned to the primary site instead of the feed’s target site. ([#658](https://github.com/craftcms/feed-me/issues/658))
- Fixed a bug where assets would always be imported to the primary site instead of the feed’s target site. ([#725](https://github.com/craftcms/feed-me/pull/725))
- Fixed a PHP error that occurred when importing content to a mapped Dropdown field. ([#732](https://github.com/craftcms/feed-me/pull/732))
- Fixed a PHP error that occurred when importing a JSON object, rather than an array. ([#761](https://github.com/craftcms/feed-me/issues/761))
- Fixed a bug where an existing enabled entry wouldn’t be disabled if a matching feed item was marked as disabled. ([#760](https://github.com/craftcms/feed-me/pull/760))
- Fixed a bug where unique identifier checkboxes had extra spacing between them. ([#727](https://github.com/craftcms/feed-me/issues/727))
- Fixed a SQL error that occurred when importing a feed item with a missing field and no default value set. ([#527](https://github.com/craftcms/feed-me/issues/527))
- Fixed a bug where an entry’s default status was ignored for a section on multi-site installs. ([#541](https://github.com/craftcms/feed-me/issues/541))
- Fixed an error that could occur when using an alternate queue driver. ([#553](https://github.com/craftcms/feed-me/issues/553))
- Fixed a bug where the final screen after editing a feed would say that the feed was being processed even though it wasn’t. ([#638](https://github.com/craftcms/feed-me/issues/638))

## 4.2.4 - 2020-10-01

### Fixed
- Fixed a bug where pagination would break if a feed provided a root relative pagination URL.
- Fixed a bug where pagination would break if a feed provided an invalid URL.  ([#694](https://github.com/craftcms/feed-me/issues/694))

## 4.2.3 - 2020-05-12

### Fixed
- Fixed a bug where all elements would be disabled if the “Add” and “Disable” strategies were used together. ([#696](https://github.com/craftcms/feed-me/issues/696))

## 4.2.2 - 2020-03-26

### Changed
- Feed Me now requires Craft 3.4 or later.

### Deprecated
- Deprecated `craft\feedme\helpers\AssetHelper::queryHash()`.

### Fixed
- Fixed a bug where the “Save” and “Save and continue” buttons were hanging off the bottom of the page. ([#664](https://github.com/craftcms/feed-me/issues/664))
- Fixed a bug where Feed Me would report that processing was complete when it wasn’t. ([#664](https://github.com/craftcms/feed-me/issues/664))
- Fixed a PHP error when importing assets from URLs with query strings, on PHP 7.4 or later. ([#682](https://github.com/craftcms/feed-me/issues/682))

## 4.2.1.1 - 2020-03-17

### Fixed
- Fixed a typo in the changelog.

## 4.2.1 - 2020-03-17

### Added
- Added support for the Entries Subset field type. ([#686](https://github.com/craftcms/feed-me/pull/686))

### Changed
- Feed importing jobs now use the queue’s default `ttr` and `attempts` settings if the `queueTtr` and `queueMaxRetry` settings haven’t been set in `config/feed-me.php`. ([#662](https://github.com/craftcms/feed-me/issues/662))

### Fixed
- Fixed some bugs with importing and displaying related entries that have a disabled status. ([#645](https://github.com/craftcms/feed-me/issues/645))

## 4.2.0.1 - 2020-01-15

### Fixed
- Fixed a bug that broke local feeds. ([#647](https://github.com/craftcms/feed-me/issues/647))

## 4.2.0 - 2020-01-02

### Changed
- Added a fail-safe when assigning default authors to entries to ensure lookup is always done against the user’s ID. ([#627](https://github.com/craftcms/feed-me/issues/627))

### Fixed
- Fixed a bug that could occur when importing default values to Checkboxes or Multi-select fields.
- Fixed support for the Linkit plugin. ([#615](https://github.com/craftcms/feed-me/issues/615))
- Fixed compatibility with Craft 3.4. ([#643](https://github.com/craftcms/feed-me/issues/643))
- Fixed a bug where Feed Me wasn’t prepared for the possibility that an event handler for `craft\feedme\services\Process::EVENT_BEFORE_PROCESS_FEED` could alter the total number of feed items to process. ([#619](https://github.com/craftcms/feed-me/pull/619))

## 4.1.2 - 2019-08-11

### Fixed
- Fixed a bug where multi-site elements were only getting saved in the site chosen in the feed settings. ([#564](https://github.com/craftcms/feed-me/issues/564))
- Fixed an error that occurred when choosing a default user photo on case-sensitive file systems.
- Fixed an error that occurred when importing a user without a photo, if a default user photo had been chosen. ([#562](https://github.com/craftcms/feed-me/issues/562))
- Fixed a bug where it wasn’t possible to install Feed Me on projects requiring PHP dotenv 3. ([#588](https://github.com/craftcms/feed-me/issues/588))

## 4.1.1 - 2019-07-06

### Changed
- It’s now possible for `onAfterParseField` event handlers to modify parsed field values by overriding the `$parsedValue` property on the event. ([#516](https://github.com/craftcms/feed-me/issues/516))

### Fixed
- Fixed an issue that could happen when trying to import tags.
- Fixed a missing PHP class import. ([#563](https://github.com/craftcms/feed-me/issues/563))

## 4.1.0 - 2019-04-24

### Added
- Added the “Disable missing elements in the target site” Import Strategy option. ([#517](https://github.com/craftcms/feed-me/issues/517))

### Changed
- Renamed the “Sites” setting to “Target Site”, which now specifies the _initial_ site that elements should be saved in, rather than the _only_ site. ([#521](https://github.com/craftcms/feed-me/issues/521))
- Improved JSON feed parsing performance. ([#510](https://github.com/craftcms/feed-me/issues/510))

### Fixed
- Fixed a bug where imported dates were assumed to be set in the system time zone even if they specified something else.
- Fixed a bug where custom message translations weren’t getting registered for JavaScript.
- Fixed a PHP error that could occur when editing or creating a feed, if Commerce, Digital Products, Calendar, or Comments were Composer-installed but not enabled.

## 4.0.0 - 2019-04-09

### Changed
- Feed Me is now available as a single, free edition.
- The package name is now `craftcms/feed-me`.
- The root namespace is now `craft\feedme`.
- Feed Me now requires Craft 3.1.21 or later.

## 3.1.17 - 2019-04-07

### Added
- Add `assetDownloadCurl` option.
- Add Tag element support.
- Add `sortOrder` for feeds.

### Changed
- Allow `beforeFetchFeed` the ability to set the feed’s response.
- Allow empty date values to be included in field data.
- Port additional boolean-like values from Craft 2 version. (thanks @jamesmacwhite).
- Create slug the same way as Craft. (thanks @smcyr).

### Fixed
- Fix Matrix mapping not applying default values.
- Refactor unique identifier check, including inherited fields.
- Error-handle a little for custom datatypes from Craft 2.
- Fix Simple Map integration.
- Ensure correct element scenario is set for element fields, when set to create.
- Fix some fields throwing errors due to namespacing.

## 3.1.16 - 2019-03-22

### Added
- Add more logging info around assets and uploading.
- Add logging info for all element fields and their matching criteria.
- Add `queueTtr` and `queueMaxRetry` for queue timeout handling.

### Fixed
- Add siteId check to category parent matching. Thanks (@pieter-janDB).
- Elements should inherit the `enabled` value for `enabledForSite` in multi-site setups.
- Add siteId migration (just in case).
- Update Craft 2>3 migration to include table updates when no feeds exist.
- Comments - add URL to mapping.

## 3.1.15 - 2019-03-09

### Added
- Add option to create folders for asset imports.

### Fixed
- Fix un-mapped fields being processed incorrectly in some cases.
- Fix assets not actually using the correct folder mapping config.
- Update schema version - just in case its an issue for C2 upgrades.
- Fix handling of asset folders when importing assets.

## 3.1.14 - 2019-03-08

### Added
- Added support for native editions.

## 3.1.13 - 2019-03-08

### Fixed
- Fix element fields with their own element fields not having their values set on feeds.
- Fix help controller field error.
- Fix parsing mapped values a little too eagerly for Matrix fields, potentially ignoring mapping for other element fields.

## 3.1.12 - 2019-03-03

### Fixed
- Ensure all complex fields don't process when none of their sub-fields are mapped.

## 3.1.11 - 2019-03-02

### Added
- Added config option to run Garbage Collection before a feed starts.

### Fixed
- Ensure complex fields (Matrix, etc) don't process when none of their sub-fields are mapped.

## 3.1.10 - 2019-02-26

### Fixed
- Allow comments owner to match on custom fields.
- Fix compatibility with latest Comments release, add missing commentDate.
- Comments - Remove unneeded custom save function thanks to `Element::SCENARIO_ESSENTIALS` disabling validation.
- Remove `Db::escapeParam()` when directly querying (not required and causing matching issues).

## 3.1.9 - 2019-02-22

### Fixed
- Add support for league/csv:^9.0, where some combinations of plugins loaded this version instead of 8.0. Caused some CSVs to show as empty.
- Ensure entries created through their fields respect their default status.
- Fix entries section not being selected properly in mapping.
- Ease up on comparing content - doesn’t need to match exact type.

## 3.1.8 - 2019-02-17

### Fixed
- Fix element fields not finding existing elements when `Relate digital products from a specific site?` was set on the field.

## 3.1.7 - 2019-02-16

### Fixed
- Fix primary element selector showing incorrect values when two nested nodes have the same name.
- Switch variant parsing behaviour to support nested levels less than 2 first.
- Remove assumption that empty arrays should be ignored.
- Ensure third-party fields check for empty data before returning.
- Ensure element fields check for empty data before returning.

## 3.1.6 - 2019-02-15

### Added
- Add console command to run feeds. Refer to [docs](https://docs.craftcms.com/feed-me/v4/feature-tour/trigger-import-via-cron.html#console-command)
- Implement log file rotation - stop them getting out of hand
- Add more date formatting options.

### Changed
- Add better content checks for number and lightswitch.
- Add better checks for element groups (user groups) content.
- Improve checking against existing date content.
- Update asset fields to use `resolveDynamicPathToFolderId` by default. Should match field settings more consistently.

### Fixed
- Ensure blank CSV rows are stripped out.
- Ensure when matching against existing data that there’s values to compare against. Prevents againt matching incorrectly.
- Try to fix `A non-numeric value encountered` error.
- Fix error in help controller when no field found.
- Fix entry/category matching against parents correctly.
- Fix Postgres error when no sources for element fields are selected.
- Ensure values trim for whitespace, if strings.

## 3.1.5 - 2019-02-11

### Changed
- Refactor `afterProcessFeed` to work with pagination.

### Fixed
- Fix lightswitch default value not saving.
- Fix typo in user profile photo uploads.
- Fix checking for entry’s default status for section.
- Fix searching for existing assets not using the prepped filename.
- Fix elements being disabled/deleted incorrectly for paginated feeds.

## 3.1.4 - 2019-02-06

### Fixed
- Fix user photo upload and issues.
- Fix syntax error in help controller.

## 3.1.3 - 2019-02-06

### Fixed
- Fixed `EVENT_BEFORE_PROCESS_FEED` change from 3.1.2 causing issues in feed processing.

## 3.1.2 - 2019-02-02

### Changed
- Support `nesbot/carbon` `^1.22 || ^2.10`
- Support `league/csv` `^8.2 || ^9.0`

### Fixed
- `EVENT_BEFORE_PROCESS_FEED` process event can actually modify variables (thanks @monachilada).
- Always include a root node for primary element.

## 3.1.1 - 2019-02-01

### Added
- Updates to asset element importing, including "URL or Path" field.
- Added docs guide for asset element importing (finally, right).

### Changed
- Add some more clarity around errors in help requests.
- Update processing events to be cancelable and modify params.
- Upgrades to nesbot/carbon ^2.10.
- Allow `getSelectOptions()` to modify the ‘none’ option.
- Alphabetise help feeds.

### Fixed
- Fix primary elements not showing all levels of options to pick from.
- Fix error occurring when `parseTwig` was set to true.
- When creating elements via fields, ensure they’re created in the same siteId as the owner element.
- Fix asset field matching local assets.
- Fix import issues when values contain data delimiter with whitespace around the delimiter.
- Updates to asset element importing, fixing a few issues.
- Fix asset fields not matching existing assets.

## 3.1.0 - 2019-01-21

### Added
- Added full support for Craft 3.1 (now also minimum version). Thanks to all the contributors!
- Added beforeSave() for elements.
- Allows SELECT_DATES to be imported for Solspace Calendar events. (thanks @samstevens).

### Changed
- Add better handling for milliseconds and seconds timestamps. Date fields can now choose which timestamp is used in their feed.
- Refactor asset element imports, particularly for remote uploads. Prevents asset duplication and fixes element fields not being populated.

### Fixed
- Add checks around third-party elements if they exist but aren’t installed.
- Add conditionals around element field layouts throwing errors in some cases.
- Fix element mapping templates throwing errors in some cases.
- Fix some element fields not matching elements properly when selecting specific types.
- Fix mapping element fields with no field layout.
- Fix empty string values not being imported into fields, in some cases, for some fields.
- Fix potential error occurring when incorrectly configuring an element and trying to proceed to mapping screen.
- Fix not fetching node content when the node contains a dot character.
- Include `resolveDynamicPathToFolderId()` in asset field matching to resolve to dynamic folders correctly.

## 3.0.2.1 - 2018-12-13

### Fixed
- Fix `SCENARIO_ESSENTIALS` error from 3.0.2.

## 3.0.2 - 2018-12-12

### Added
- Added `logging` config option.

### Fixed
- Fix `SCENARIO_ESSENTIALS` not being applied for products and comments.
- Fix elements being disabled/deleted with `compareContent` on.

## 3.0.1 - 2018-12-06

### Changed
- All element titles will be truncated automatically if over 255 characters.

### Fixed
- Fix SQL error when setting a default author.
- Fix `matchExistingElement` not supporting false-y values.
- Fix Matrix/Super Table support for nested (complex) fields.
- Fix SQL error ocurring when mapping a parent entry.
- Fix Assets (and other) elements not having their modified data saved properly in some instances.

## 3.0.0 - 2018-11-28

### Added
- Add JSON linter for better parsing errors.
- Add `composer.json` and `composer.lock` to support requests.
- Add `onAfterParseFeed` event.
- Add `sleepTime` config setting, to set `sleep()` on each feed processing.

### Changed
- Selecting a default author now uses an element select field.
- Clarify suspending users with disabling elements.
- Make a few more events modifiable.

### Fixed
- Fix template tags not working properly.
- Fix pagination URL not saving.
- Assets - ensure existing element is set/updated when fetching image.
- Fix matrix fields not grouping content correctly for JSON feeds.
- Ensure element fields, when set to create, respect the feed propagation settings.
- Return empty array when no data for element fields
- Do not make redirect request after install if install is done via CLI. Thanks (@nettum).
- Fix for some fields not saving their mapping options inside Matrix.

## 3.0.0-beta.30 - 2018-11-15

### Fixed
- Fix error thrown by datatypes when using template tags (properly).

## 3.0.0-beta.29 - 2018-11-15

### Fixed
- Fix user status not working correctly, or throwing an error if setting to active.
- Fix content checks not comparing existing content correctly.
- Fix error thrown by datatypes when using template tags.
- Refactor and fix variants not working with Matrix fields (and other complex fields).

## 3.0.0-beta.28 - 2018-11-13

### Fixed
- Fix conflict with Navigation plugin migration

## 3.0.0-beta.27 - 2018-11-13

### Added
- Added pagination handling for feeds. Select a node in your feed that contains a URL to the next set of data for your content, and Feed Me will automatically fetch it.
- Added content comparison functionality, where Feed Me will look at all your existing content for an element, compare it, and only proceed if content has changed. This brings massive performance improvements by not needlessly updating elements. Also controlled through `compareContent` as a config setting, default to true.
- Added Google Sheet as a datatype.
- Added type switch for logs - filter your logs by info, error or all.
- Added `feedOptions` config for feed-specific settings. Control request headers per-feed, or change any feed attribute in your configuration file.

### Changed
- Set `SCENARIO_ESSENTIALS` scenario when saving an element. In-line with how Craft handles bulk element updates, and to integrate nicely with [SEOmatic](https://github.com/nystudio107/craft-seomatic).
- All element fields now return unique IDs.
- Provide more detail for XML parsing errors.
- Users - set user to be suspended when disabling elements.

### Fixed
- Fix enabled tabs throwing an error in some cases.
- Fix error thrown when matching elements on their ID.
- Fix not matching entries and categories across multi-sites.
- Fix not being able to select filename as an identifier for asset fields.
- Cleanup and properly sort settings and config options.
- Move extra element attribute setting within parseTwig conditional, ensuring elements are bound twice with attribute and field data.
- Fix categories fields not limiting per their field settings.
- Add serialise and normalise for default field content, particularly useful with Redactor.
- Fix additional Linkit data being added to import.
- Implement table field sub-field validations.
- Fix Table fields not containing all columns when null values.
- Fix Matrix including order and not setting collapsed/disabled to boolean.
- Fix Matrix not handling scenario when only adding content to one block type.

## 3.0.0-beta.26 - 2018-11-01

### Changed
- Limit logs entries in the UI to 300.
- Switch `dataDelimiter` from `|` to `-|-` - causing too many issues with Twig.
- Twig parsing in field content or default values is now opt-in. Use an array of field handles or attribute handles in a config setting `parseTwig`.

### Fixed
- Fix error when trying to match against custom field in category, entry and user fields.
- Allow to map against Preparse element field content.
- Fix missing FeedMe class definitions.
- Protect debug output from console requests.
- Fix element field matching.

## 3.0.0-beta.25 - 2018-10-26

### Changed
- All new logging! More logging and more details.

### Fixed
- Fixed an error with Super Table fields (thanks @jaydensmith).

## 3.0.0-beta.24 - 2018-10-24

### Changed
- Tighten restrictions on what can be a unique identifier field.
- Improve local file handling a little and relative paths.
- CSV - allow files without headers to still be processed instead of throwing an error.

### Fixed
- AssetHelper - add checks for spaces in filenames.
- Add array-handling to default fields, preventing errors like `trim()`, etc.
- Add some conditionals to migration from Craft 2 > 3.
- Fix template error when updating Craft 2 > 3.

## 3.0.0-beta.23 - 2018-10-23

### Added
- Added support for [Digital Products](https://github.com/craftcms/digital-products) element and field.
- Added support for [Solspace Calendar](https://github.com/solspace/craft3-calendar) element and field.
- Added support for [Comments](https://github.com/verbb/comments) element.
- Added support for [Super Table](https://github.com/verbb/super-table) field.
- Added support for [Linkit](https://github.com/fruitstudios/craft-linkit) field.
- Added support for [Typed Link](https://github.com/sebastian-lenz/craft-linkfield) field.
- Added support for [SimpleMap](https://github.com/ethercreative/simplemap) field.
- Add offset/limit options to template params

### Changed
- Matrix handling, particularly for XML-based feeds are much more opinionated about structure for better results. See [docs](https://docs.craftcms.com/feed-me/v4/guides/importing-into-matrix.html#note-on-structure).

### Fixed
- Fix element fields in Matrix not mapping correctly.
- Fix Twig parsing in default and feed data too early, resulting in empty values.
- Matrix - fix block types with no fields messing things up.
- Fix ‘placeholder’ in products query causing PostgreSQL errors.
- Fix error thrown on entry mapping screen when no sections are available.
- Assets - fix filename matches not including subfolders.
- Table - protect against array values importing into fields.

## 3.0.0-beta.22 - 2018-10-04

### Fixed
- Fixed an error when trying to match relational entries via a custom field.
- Fix integrity constraint error thrown by author.

## 3.0.0-beta.21 - 2018-08-21

### Fixed
- Fix typos in element classes

## 3.0.0-beta.20 - 2018-08-21

### Changed
- Drop support for email fields for users (potentially causing conflicts)

### Fixed
- Fix incorrect tab URLs
- Error checking entry section
- Fix entries field error when trying to access a section has been deleted
- Fix element-creation not finding existing elements of any status

## 3.0.0-beta.19 - 2018-08-18

### Changed
- Ensure element fields don’t throw fatal errors when unable to save - allowing owner element to continue.
- Products - remove required attribute on unlimited stock.
- Change element field matching existing elements querying. Fixes the case where trying to match elements with the keyword 'not' in the value.

### Fixed
- Fix primary element iterator when only one item in feed (in some cases).
- Fix enabled tabs in CP not working.
- Fix error thrown for table field when no delimiter defined.
- Fix for inner-element fields for entries throwing an error.
- Fix Matrix blocks not being sorted correctly in cases where they’re paired with element fields.

## 3.0.0-beta.18 - 2018-08-16

### Added
- Add support for Table fields and `dataDelimiter` for multiple rows.
- Allow commerce variants to set their enabled state.
- Add ability to store base64 encoded assets in addition to URLs (thanks @urbantrout).
- Added config option for csv delimiter `csvColumnDelimiter` (thanks @crollalowis)

### Changed
- Improve user-creation handling for user fields.
- Matrix - Refactor parsing logic to be (hopefully) better.
- Matrix - swap enabled with disabled checkbox for blocks.
- Improve product variants, preventing orphans in some cases.
- Provide field option for handling existing user groups. Either fully replace existing user groups, or append.
- Prevent elements from propagating when targeting a site.

### Fixed
- Assets - fix incorrect skipping of existing assets when there aren’t necesarily any found.
- Table - Fix processing changes when no delimiter (not required).
- Use registerTwigExtension(), otherwise may cause Twig to be loaded before it should be (thanks @brandonkelly)
- Entry - Fix authors not being created when they should be.
- CSV - fix for line breaks in headings causing issues.
- Fix for variants in that they can potentially move to another product type, or otherwise plucked from a product other than the one you're importing.
- Fix incorrect variant custom field namespace.

## 3.0.0-beta.17 - 2018-07-19

### Changed
- Replace `getReferrer()` which redirects inconsistently.
- Add item/row number into log message.

### Fixed
- Fix edge-case for importing a single item (and only one).
- Ensure values are escaped when comparing existing elements.
- Products - ensure attributes are set properly in all instances.
- Products - fix custom fields not being set on variants.
- Fix `Undefined variable: variants`.
- Fix miss-spelling of `dataDelimiter`.
- Fix invalid reference to TagElement in Users element class.
- Fix user status not being able to bet set via the element anymore.
- Protect against setting element attributes when null.

## 3.0.0-beta.16 - 2018-07-07

### Added
- Add support for element fields to match on simple custom fields
- Allow element fields to select default elements
- Add config option for data delimeter (`dataDelimiter`)

### Changed
- Add `prepareAssetName` to asset handler to handle filenames better

### Fixed
- Fix help errors ocurring in some cases
- Fix date format parsing
- Fix date fields not passing formatting options to helper
- Add safety checks for boolean values needing to be a string
- Fix some element attributes not being set correctly
- Fix default value and values not respecting falsey values in field mapping
- Fix product variants not using the default value
- Fix product variant data not showing in debug
- Fix `services/Fields.php::parseField()` unknown property `$feed` is set.
- Fix dropdown field (and others) not respecting the default value
- Commerce - fix missing `BaseHelper`

## 3.0.0-beta.15 - 2018-06-12

### Added
- Commerce Element support
- Commerce Products field support
- Commerce Variants field support
- Added more default options for fields

### Changed
- Improve process service by allowing events to modify variables

### Fixed
- Fixed handling of lightswitch fields
- Fix element-matching not throwing an error when it should
- Entry - Fix parent not being created when checked
- Entry - Don’t show section options for parent
- Entry/Category - Support `targetSiteId` setting
- Element fields should match existing elements regardless of (site) status
- Allow multi-site entry/category fields to match correctly (for the same site)
- Minor visual fix for mapping checkboxes
- Assets - Fix for asset-upload filename. Did not take into account query string when creating a filename from a dynamically generated URL
- Minor fix for PHP 7.2 when no items are available to process in the feed

## 3.0.0-beta.14 - 2018-05-23

### Fixed

- Minor fix for table column ordering
- Fix lack of parent category-creation
- Add logging for category creation
- Fix handling of entries fields when limited to singles
- Fix table mapping not using the correct column handle

## 3.0.0-beta.13 - 2018-05-08

### Fixed

- Fix for feed items not continuing after encountering a processing error

## 3.0.0-beta.12 - 2018-05-08

### Added

- Add support for [Smart Map](https://github.com/doublesecretagency/craft-smartmap)

### Fixed

- Improve CSV handling, particular for Windows-generated files which can have encoding issues
- Fix bug with not being able to select all primary elements
- Fix PHP 7.2 warnings
- Improve performance of content parsing for attributes and custom fields

## 3.0.0-beta.11 - 2018-05-05

### Fixed

- Fixed error thrown by unique identifier (caused in beta.10)

## 3.0.0-beta.10 - 2018-05-04

### Added

- Support aliases in feed URL
- Support date as unique identifier

### Fixed

- Fix relative paths not working
- Fix matching existing elements with special characters
- Improve handling of remote asset handling when `HEAD` requests fail
- Fix help widget
- Improve date-helper to handle ‘0’
- Table - ensure dates are parsed

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
- Fixed an issue when triggering feeds from CLI. ([#262](https://github.com/craftcms/feed-me/pull/262))
- Fix for date attributes not checking for falsey values before returning current date.
- Fix for locale entries not having their status set as per the default section status.

## 2.0.9 - 2017-10-18

### Fixed
- Fixed icon mask.
- Fixed incorrect license servers for new licenses.

## 2.0.8 - 2017-10-17

### Added
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
- Properly support third-party data types. ([#172](https://github.com/craftcms/feed-me/pull/172))
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
- Added support for non-http protocols for feeds (ftp://, file://, etc). ([#29](https://github.com/craftcms/feed-me/issues/29))

## 1.4.6 - 2016-01-19

### Fixed
- Fixed an issue where an error would be incorrectly thrown when Add duplication handling is used.

## 1.4.5 - 2016-01-13

### Fixed
- Fixed issue with plugin release feed url.

## 1.4.4 - 2015-12-27

### Fixed
- Fixed issue with irregular nested elements. ([#24](https://github.com/craftcms/feed-me/issues/24#issuecomment-167106972))

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
