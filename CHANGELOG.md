# Release Notes for Feed Me

## Unreleased
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
