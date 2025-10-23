# Release Notes for Feed Me

## Unreleased

- Fixed an error that could occur when setting up mappings if a field had been deleted, but not removed from the field layout. ([#1687](https://github.com/craftcms/feed-me/pull/1687))
- Fixed a bug where some imported assets would end up with a double file extension. ([#1688](https://github.com/craftcms/feed-me/pull/1688))
- Fixed a bug where `setEmptyValues` wasn’t being respected for Matrix Blocks in some scenarios. ([#1689](https://github.com/craftcms/feed-me/pull/1689))

## 6.10.1 - 2025-08-27

- Fixed a PHP error that would occur on the feed mapping page on some installations. ([#1678](https://github.com/craftcms/feed-me/pull/1678))
- Fixed a PHP error that could occur on the feed mapping page when processing an entry type that has an entries field linked to a single. ([#1675](https://github.com/craftcms/feed-me/pull/1675))

## 6.10.0 - 2025-08-15

- Added support for importing into Craft’s native Alternative Text field for Assets. ([#1470](https://github.com/craftcms/feed-me/pull/1460))
- Added support for importing into Craft’s native Address field. ([#1470](https://github.com/craftcms/feed-me/pull/1460))
- Added the ability to map the Promotional Price when importing Commerce Products. ([#1665](https://github.com/craftcms/feed-me/pull/1665))
- Added `craft\feedme\base\Field::populateNativeFields()`.
- Added `craft\feedme\events\RegisterFeedMeFieldsEvent::$nativeFields`.
- Added `craft\feedme\fieldlayoutelements\addresses\AddressField`.
- Added `craft\feedme\fieldlayoutelements\addresses\FullNameField`.
- Added `craft\feedme\fieldlayoutelements\addresses\LatLongField`.
- Added `craft\feedme\fieldlayoutelements\assets\Alt`.
- Added `craft\feedme\fieldlayoutelements\users\Addresses`.
- Added `craft\feedme\services\Fields::EVENT_BEFORE_PARSE_NATIVE_FIELD`.
- Added `craft\feedme\services\Fields::EVENT_AFTER_PARSE_NATIVE_FIELD`.
- Added `craft\feedme\services\Fields::getRegisteredNativeField()`.
- Added `craft\feedme\services\Fields::getRegisteredNativeFields()`.
- Added `craft\feedme\services\Fields::parseNativeField()`.
- Fixed a bug where you could not import Commerce Variant data into a field that specified Product Type sources. ([#1652](https://github.com/craftcms/feed-me/pull/1652))
- Fixed a bug where feed data could differ when running the feed with debugging enabled. ([#1670](https://github.com/craftcms/feed-me/pull/1670))
- Fixed a PHP error that could occur when importing an array of elements into a related field when using the Debug option. ([#1667](https://github.com/craftcms/feed-me/pull/1667))

## 6.9.0 - 2025-08-10

- Added a `Process::EVENT_COMPARE_CONTENT` to give plugins a chance to manipulate feed data before any content comparison is done. ([#1623](https://github.com/craftcms/feed-me/pull/1623))
- Added support for importing custom options to the Radio Buttons and Checkboxes field types. ([#1658](https://github.com/craftcms/feed-me/pull/1658))
- Fixed an error that could occur when running a feed with nothing to process. ([#1611](https://github.com/craftcms/feed-me/pull/1611))
- Fixed a bug where duplicate users could get created when importing entry authors with a matching email address. ([#1612](https://github.com/craftcms/feed-me/pull/1612))
- Fixed a bug where importing into a Matrix field with “Use default value” selected, subfields could use the default value in certain scenarios. ([#1613](https://github.com/craftcms/feed-me/pull/1613))
- Fixed a bug that could occur on PostgreSQL when using the `setEmptyValues` feed setting. ([#1620](https://github.com/craftcms/feed-me/pull/1620))
- Fixed a bug that could occur when saving empty ("") values if `compareContent` and “Set Empty Values” were both enabled. ([#1621](https://github.com/craftcms/feed-me/pull/1621))
- Fixed a bug that could occur when importing into a Matrix field with no blocks. ([#1622](https://github.com/craftcms/feed-me/pull/1622))
- Fixed a bug that would happen when matching elements on the keywords `and` and `or`. ([#1629](https://github.com/craftcms/feed-me/pull/1629))
- Fixes a bug where importing _only_ a title for a Matrix entry would result in that nested entry not being imported. ([#1630](https://github.com/craftcms/feed-me/pull/1630))
- Fixed a bug where custom Guzzle configuration would not be respected when processing a feed. ([#1647](https://github.com/craftcms/feed-me/pull/1647))
- Fixed an issue that could occur when running garbage collection before importing a feed when using PostgreSQL. ([#1632](https://github.com/craftcms/feed-me/pull/1632))
- Fixed a regression that happened in 5.3.0 where imports into relational field would not remove existing relations if it was required. ([#1623](https://github.com/craftcms/feed-me/pull/1623))
- Fixed a regression where `EVENT_BEFORE_PROCESS_FEED` was called once per batch and not once per page. ([#1628](https://github.com/craftcms/feed-me/pull/1628))
- Fixed a regression where the feed name prefix was no longer showing up in the Logs tab in the Control Panel. ([#1660](https://github.com/craftcms/feed-me/pull/1660))

## 6.8.0 - 2025-03-14
- 
- Added support for Craft’s batched queue jobs, so that long-running feeds can be processed in batches. ([#1598](https://github.com/craftcms/feed-me/pull/1598))
- Improved support for importing into Time fields. ([#1595](https://github.com/craftcms/feed-me/pull/1595))
- Fixed a PHP error that could occur with some plugins that were installed, but Feed Me doesn't support. ([#1596](https://github.com/craftcms/feed-me/pull/1596))
- Fixed a bug where that could occur that would prevent importing Commerce Variants. ([#1605](https://github.com/craftcms/feed-me/pull/1605))

## 6.7.0 - 2025-01-07

- Feed Me now requires Craft CMS 5.6.0 or later.
- Added support for viewing read-only settings on environments where `allowAdminChanges` is disabled. ([#1592](https://github.com/craftcms/feed-me/issues/1592))
- Improved the experience of mapping relational fields with individual field instances in Craft 5. ([#1585](https://github.com/craftcms/feed-me/issues/1585))
- Feed Me settings are now only available to admins and when `allowAdminChanges` is set to `true`. ([#1581](https://github.com/craftcms/feed-me/pull/1581))
- Added support for Craft [Time](https://craftcms.com/docs/5.x/reference/field-types/time.html) fields. ([#1593](https://github.com/craftcms/feed-me/pull/1593))
- Added support for `formatted` and `raw` subfields in the Google Maps plugin. ([#1587](https://github.com/craftcms/feed-me/pull/1587))
- Fixed a bug where excessive logs would be generated in the Feed Me logs table in the database. ([#1594](https://github.com/craftcms/feed-me/pull/1594))
- Fixed a bug where empty values were not respected for inner-element fields when `setEmptyValues` is set to `true`. ([#1590](https://github.com/craftcms/feed-me/pull/1590))
- Fixed a bug where values with an empty string would not be treated as empty if `setEmtpyValues` is set to `true`. ([#1591](https://github.com/craftcms/feed-me/pull/1591))

## 6.6.1 - 2024-12-01

- Fixed a PHP error that could occur if you did not have Commerce installed when running a feed. ([#1556](https://github.com/craftcms/feed-me/issues/1556)) 

## 6.6.0 - 2024-11-26

- Added the `assetDownloadGuzzle` config setting which defaults to `false`. When it is set to `true`, Feed Me will use Guzzle to download assets instead curl directly. ([#1549](https://github.com/craftcms/feed-me/pull/1549))
- Imported Commerce Products now add a single Catalog Pricing job to the queue after an import, instead of one per Product. ([#1547](https://github.com/craftcms/feed-me/pull/1547))
- Fixed a bug where importing matching Categories could go awry if the source of the field is set to a group and not a custom source. ([#1550](https://github.com/craftcms/feed-me/pull/1550))
- Fixed a bug when importing Category fields that started with the string `not`. ([#1551](https://github.com/craftcms/feed-me/pull/1551))

## 6.5.0 - 2024-10-15

- Added support for propagating Commerce Products and Variants in multi-site installs. ([#1531](https://github.com/craftcms/feed-me/pull/1531))
- When duplicating a feed, a new passkey is generated instead of using the passkey on the original feed. ([#1534](https://github.com/craftcms/feed-me/pull/1534))

## 6.4.1 - 2024-10-09

- Fixed a PHP error that would occur when importing Products that had new Variants. ([#1528](https://github.com/craftcms/feed-me/pull/1528))
- Fixed a bug where Lightswitch fields would not import correctly when nested in a Matrix or Super Table field. ([#1529](https://github.com/craftcms/feed-me/pull/1529))
- Fixed a bug where Commerce Variant attributes were not respecting the `parseTwig` config setting. ([#1326](https://github.com/craftcms/feed-me/pull/1326))

## 6.4.0 - 2024-09-25

- Added support for importing into relational fields that have custom sources selected. ([#1504](https://github.com/craftcms/feed-me/pull/1504))
- Fixed a bug that could occur when uploading files to an Assets field from an external URL and a new filename is provided, but we can't determine the remote file's extension. ([#1506](https://github.com/craftcms/feed-me/pull/1506))
- Fixed a bug where the fields available to map within a given Entries field could not match the fields from that Entry type's field layout. ([#1503](https://github.com/craftcms/feed-me/pull/1503))
- Fixed a bug where you could not import an Asset field into a Matrix field if it had a dynamic subpath set. ([#1501](https://github.com/craftcms/feed-me/pull/1501))
- Fixed a bug where the stock was not updating when importing Product Variants. ([#1490](https://github.com/craftcms/feed-me/pull/1490))

## 6.3.0 - 2024-08-14

- Added a `feed-me/logs/clear` console command to clear database logs.
- Fixed a bug where the logs table would not load with a large number of logs.

## 6.2.2 - 2024-08-14

- Fixed a bug where un-redacted environment variables were being logged to the database. ([#1491](https://github.com/craftcms/feed-me/issues/1491))

## 6.2.1 - 2024-07-18

- Fixed a PHP error that could occur when importing Assets that had a missing filename. ([#1481](https://github.com/craftcms/feed-me/pull/1481))
- Fixed a bug that could occur when importing into a Dropdown field that did not support empty strings as a value and the feed had an empty string. ([#1484](https://github.com/craftcms/feed-me/pull/1484))

## 6.2.0 - 2024-07-09

> [!WARNING]
> - After updating, you will need to re-map and re-save any feeds that use a Matrix field with a nested complex fields (Google Maps, Table, etc.).
> - Feed Me now logs to the database by default.
>   - This may lead to an increase in database size if logs are not cleared. To customize this behavior, see [Customizing Logs](README.md#customizing-logs).
>   - Consider configuring the `logging` setting to `'error'` to reduce logs.

- Fixed a bug where complex fields (Google Maps, Table, etc.) would not import correctly when nested inside of a Matrix field. ([#1475](https://github.com/craftcms/feed-me/pull/1475))
- Fixed a PHP error that could occur when importing Entries or Categories with “Default Author” set on the feed mapping. ([#1476](https://github.com/craftcms/feed-me/pull/1476))
- Fixed a bug where simple value comparisons would fail if the value you were checking against was missing. ([#1473](https://github.com/craftcms/feed-me/pull/1473))
- Fixed a bug where assets imported into a Matrix field with “Use this filename for assets created from URL” set would duplicate the first asset across all Matrix blocks. ([#1472](https://github.com/craftcms/feed-me/pull/1472))
- Fixed a bug where the “Disable missing elements globally” setting was only working for the primary site. ([#1474](https://github.com/craftcms/feed-me/pull/1474))
- Fixed an error that would occur when running a feed with the backup database setting enabled, when Craft's `backupCommand` was set to false. ([#1461](https://github.com/craftcms/feed-me/pull/1461))
- Logs now use the default log component, and are stored in the database. [#1344](https://github.com/craftcms/feed-me/issues/1344)

## 6.1.0 - 2024-05-26

- Added Craft Commerce 5 compatibility. ([#1448](https://github.com/craftcms/feed-me/pull/1448/))
- You can now match elements in a feed via their Asset IDs, instead of just the filename. ([#1327](https://github.com/craftcms/feed-me/pull/1327))
- Fixed a PHP error that could occur when importing multiple values into a relational field in some scenarios. ([#1436](https://github.com/craftcms/feed-me/pull/1436))
- Fixed a SQL error that could occur when matching elements on a custom field. ([#1437](https://github.com/craftcms/feed-me/pull/1437))
- Fixed a bug where mapping a relational field that has the “maintain hierarchy” setting enabled would give false positives when comparing the contents of the field. ([#1442](https://github.com/craftcms/feed-me/pull/1442))
- Fixed a bug that could occur when importing numeric values into a multi-select field.  ([#1444](https://github.com/craftcms/feed-me/pull/1444))
- Fixed a bug where a feed’s title could leak when processing a direct feed and there was a validation error. ([#1445](https://github.com/craftcms/feed-me/pull/1445))
- Fixed a bug where Date fields could cause false positives when comparing their values. ([#1447](https://github.com/craftcms/feed-me/pull/1447))
- Fixed a PHP error that could occur when matching feed nodes against element IDs. ([#1440](https://github.com/craftcms/feed-me/pull/1440))
- Fixed a bug when importing into a Matrix field with only a title and no custom fields. ([#1452](https://github.com/craftcms/feed-me/pull/1452))
- Fixed a MySQL error that could occur when you have a _lot_ of field mapping data. ([#1446](https://github.com/craftcms/feed-me/pull/1446))

## 6.0.1 - 2024-05-01

- Fixed a PHP error that would occur when importing Assets ([#1427](https://github.com/craftcms/feed-me/pull/1427))
- Fixed a PHP error that could occur when importing into an entry type with a hidden title attribute. ([#1423](https://github.com/craftcms/feed-me/pull/1423))

## 6.0.0 - 2024-03-19

- Feed Me now requires Craft CMS 5.0.0-beta.2 or later.
- Added support for importing into Icon fields.
