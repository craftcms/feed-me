# Release Notes for Feed Me

## Unreleased

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