# Release Notes for Feed Me

## Unreleased

- Fixed a bug where importing into an entry type with hidden title attribute would cause an error. ([#1423](https://github.com/craftcms/feed-me/pull/1423))

## 6.0.0 - 2024-03-19

- Feed Me now requires Craft CMS 5.0.0-beta.2 or later.
- Added support for importing into Icon fields.

## 5.4.0 - 2024-02-25

- Feed Me now requires Craft 4.6 or later.
- Added support for importing into the native [Country](https://craftcms.com/docs/4.x/country-fields.html) field. ([#1410](https://github.com/craftcms/feed-me/pull/1410))
- Fixed a bug where the Entry Type wouldn’t be remembered for related Entry fields in a feed’s mappings. ([#1387](https://github.com/craftcms/feed-me/issues/1387), [#1390](https://github.com/craftcms/feed-me/pull/1390))

## 5.3.0 - 2023-11-27

- Added the ability to provide a filename for importing assets via the assets field if you choose to “Create asset from URL”. ([#749](https://github.com/craftcms/feed-me/issues/749))
- Fixed a bug where you could not override feed settings with `false` if you used a `config/feed-me.php` config file. ([#1352](https://github.com/craftcms/feed-me/issues/1352))
- Fixed a bug where `queueTtr`, `queueMaxRetry`, and `assetDownloadCurl` settings were being ignored if you used a `config/feed-me.php` config file. ([#1356](https://github.com/craftcms/feed-me/issues/1356))
- Fixed a PHP error that could occur when you map an entry field with a default value and there is an empty value in the feed. ([#1250](https://github.com/craftcms/feed-me/pull/1250))
- Fixed a bug where the `maxRelations` setting was not being respected for relational fields. ([#1354](https://github.com/craftcms/feed-me/issues/1354))
- Fixed a bug where importing assets with “Use existing assets” could create duplicate assets. ([#1348](https://github.com/craftcms/feed-me/issues/1348))
- Fixed a bug where importing Address data from the Google Maps plugin could cause duplicate Addresses if the Address already existed. ([#1370](https://github.com/craftcms/feed-me/pull/1370))
- Fixed a bug where updating entry statuses for a site in a site group, would update statuses for all sites in the site group instead of the targeted one. ([#1208](https://github.com/craftcms/feed-me/issues/1208))
- Fixed a bug where you could not use `false` to override feed settings in `config/feed-me.php`. ([#1380](https://github.com/craftcms/feed-me/issues/1380))
- Fixed a bug where importing nested element fields would not work in some scenarios. ([#1378](https://github.com/craftcms/feed-me/issues/1378))
- Fixed a bug where empty column headings in a feed would cause incorrect values to be selected in the pagination URL dropdown when mapping a feed. ([#1375](https://github.com/craftcms/feed-me/issues/1375))
- Fixed a bug that could occur when using Feed Me with fields that have been entrified in Craft. ([1346](https://github.com/craftcms/feed-me/pull/1346)).

## 5.2.0 - 2023-07-06

- The “Create if they do not exist” setting on a feed’s mapping now conditionally show/hide the checkbox for Categories, Entries and Users relation fields. ([#1077](https://github.com/craftcms/feed-me/issues/1077))
- Fixed a bug where `setEmptyValues` wasn’t being respected when mapping Redactor and CKEditor fields. ([#1321](https://github.com/craftcms/feed-me/issues/1321))
- Fixed a bug where an element’s status would not update on a multi-site install if the status was the only thing that changed. ([#1310](https://github.com/craftcms/feed-me/issues/1310))
- Fixed a bug where the `DataTypes::EVENT_AFTER_FETCH_FEED` event was not being fired for local feeds. ([#1101](https://github.com/craftcms/feed-me/issues/1101))
- Fixed a PHP error that could happen when importing into a User field. ([#844](https://github.com/craftcms/feed-me/issues/844))
- Fixed a bug where slugs imported from a feed would not be respected if they contained underscores. ([#603](https://github.com/craftcms/feed-me/issues/603))
- Fixed a bug where nested subcategories sharing the same slug but with different parents would only import the last subcategory. ([#780](https://github.com/craftcms/feed-me/issues/780))
- Fixed a bug where you could not import a Category, Entry, Product, or Digital Product with a “0” as the title or slug. ([#1102](https://github.com/craftcms/feed-me/issues/1102))
- Fixed a bug where feeds would break for elements that had been “[entrified](https://craftcms.com/blog/entrification)”. ([#1340](https://github.com/craftcms/feed-me/issues/1340))

## 5.1.4 - 2023-06-15

- Fixed a bug where Lightswitch and Number fields could get incorrect results in an import if `setEmptyValues` was set to `false` in a feed’s settings. ([#1329](https://github.com/craftcms/feed-me/issues/1329), [#1330](https://github.com/craftcms/feed-me/pull/1330))

## 5.1.3.1 - 2023-05-02

- Fixed a bug where custom fields would not import on PostgreSQL.

## 5.1.3 - 2023-04-28

> **Warning**
> If you have an existing Commerce Products feed, you may need to resave its settings after updating.

- Improved feed mapping performance for large category groups and structure sections. ([#1255](https://github.com/craftcms/feed-me/issues/1255), [#1293](https://github.com/craftcms/feed-me/pull/1293))
- Improved Matrix field importing performance. ([#1291](https://github.com/craftcms/feed-me/issues/1291), [#1307](https://github.com/craftcms/feed-me/pull/1307))
- Fixed a bug where “Use default value” wasn’t mapping boolean fields properly. ([#1304](https://github.com/craftcms/feed-me/issues/1304), [#1305](https://github.com/craftcms/feed-me/pull/1305))
- Fixed a bug where imported categories could become orphaned if their parents were disabled. ([#555](https://github.com/craftcms/feed-me/issues/555))
- Fixed a bug where pagination wasn’t working for feeds whose URL contained an alias. ([#1244](https://github.com/craftcms/feed-me/issues/1244), [#1301](https://github.com/craftcms/feed-me/pull/1301))
- Fixed a PHP error that could occur when importing some feeds. ([#1298](https://github.com/craftcms/feed-me/issues/1298), [#1299](https://github.com/craftcms/feed-me/pull/1299))
- Fixed a PHP error that could occur when importing a Craft Commerce product feed that contained “Post Date” or Expiry Date” fields. ([#1287](https://github.com/craftcms/feed-me/issues/1287), [#1296](https://github.com/craftcms/feed-me/pull/1296))
- Fixed a bug where the mapping UI for Table fields didn’t include default value options. ([#1254](https://github.com/craftcms/feed-me/issues/1254), [#1300](https://github.com/craftcms/feed-me/pull/1300))
- Fixed a bug where date and time values weren’t getting imported into Table fields properly. ([#604](https://github.com/craftcms/feed-me/issues/604), [#1300](https://github.com/craftcms/feed-me/pull/1300))
- Fixed a bug where users’ “Preferred Locale” settings weren’t getting imported properly. ([#612](https://github.com/craftcms/feed-me/issues/612), [#1289](https://github.com/craftcms/feed-me/pull/1289))
- Fixed a PHP error occurred if Feed Me’s logging level was set to `error`. ([#1295](https://github.com/craftcms/feed-me/issues/1295), [#1297](https://github.com/craftcms/feed-me/pull/1297))
- Fixed a bug where entries with a custom propagation method weren’t getting imported properly. ([#1279](https://github.com/craftcms/feed-me/issues/1279), [#1292](https://github.com/craftcms/feed-me/pull/1292))

## 5.1.2 - 2023-04-17

- Added support for importing into CKEditor as an inner element field.
- Fixed a PHP error that could occur when saving a feed using an Asset element type with no volume selected.
- Fixed a PHP error that could occur when importing a feed  that has `setEmptyValues` set to off on the feed. ([#1269](https://github.com/craftcms/feed-me/issues/1269))
- Fixed several bugs related to empty and non-existent feed values and the “Set Empty Values” feed setting. ([#1271](https://github.com/craftcms/feed-me/pull/1271))
- Fixed a bug where that prevented importing data as Commerce Variants. ([#464](https://github.com/craftcms/feed-me/issues/464), [#1168](https://github.com/craftcms/feed-me/issues/1168))
- Fixed a bug were you could not import into Redactor as an inner element field.
- Fixed a bug where mapping into fields that support inner elements that were inside of Matrix, content from the first element was used to populate all other elements. ([#1227](https://github.com/craftcms/feed-me/issues/1227), [#1278](https://github.com/craftcms/feed-me/pull/1278))
- Fixed a bug where importing a user with an empty user photo in the feed, would assign an incorrect photo for the user. ([#582](https://github.com/craftcms/feed-me/issues/582), [#1283](https://github.com/craftcms/feed-me/pull/1283))
- Fixed a bug where importing into Matrix sub fields could use incorrect data when “Use default value” was selected for the field. ([#674](https://github.com/craftcms/feed-me/issues/674), [#1282](https://github.com/craftcms/feed-me/pull/1282))
- Removed the “Collapsed” checkbox from Matrix feed mapping screens. ([#709](https://github.com/craftcms/feed-me/issues/709), [#1284](https://github.com/craftcms/feed-me/pull/1284))
- Fixed an XSS vulnerability.

## 5.1.1.1 - 2023-03-24

- Fixed a PHP error that could occur when importing into some 3rd party fields. ([#1264](https://github.com/craftcms/feed-me/issues/1264), [#1265](https://github.com/craftcms/feed-me/pull/1265))

## 5.1.1 - 2023-03-20

- Fixed a JavaScript error that would occur on case-sensitive filesystems when using Feed Me. ([#1260](https://github.com/craftcms/feed-me/pull/1260), [#1257](https://github.com/craftcms/feed-me/issues/1257), [#1258](https://github.com/craftcms/feed-me/issues/1258), [#1259](https://github.com/craftcms/feed-me/issues/1259))

## 5.1.0 - 2023-03-17

> **Warning**
> If you have an existing Google Maps feed, you may need to remap its fields after updating.

- Added the “Set Empty Values” feed setting, which determines whether empty values in the feed should be respected or ignored. ([#1228](https://github.com/craftcms/feed-me/pull/1228), [#797](https://github.com/craftcms/feed-me/issues/797), [#723](https://github.com/craftcms/feed-me/issues/723), [#854](https://github.com/craftcms/feed-me/issues/854), [#680](https://github.com/craftcms/feed-me/issues/680))
- Added support for Money fields.
- Added support for users’ Full Name fields. ([#1235](https://github.com/craftcms/feed-me/pull/1235))
- Changes made to `craft\feedme\events\ElementEvent::$parsedValue` via `craft\feedme\base\Element::EVENT_AFTER_PARSE_ATTRIBUTE` are now respected. ([#1172](https://github.com/craftcms/feed-me/pull/1172))
- Disabled elements are no longer redundantly re-disabled, drastically improving the performance of some feed imports. ([#1248](https://github.com/craftcms/feed-me/pull/1248), [#1241](https://github.com/craftcms/feed-me/issues/1241))
- Fixed a bug where some feed element data would be considered changed even if there were no changes. ([#1220](https://github.com/craftcms/feed-me/pull/1220), [#1219](https://github.com/craftcms/feed-me/issues/1219), [#1223](https://github.com/craftcms/feed-me/pull/1223/), [#1219](https://github.com/craftcms/feed-me/issues/1219))
- Fixed a bug where the default value modal for relational fields on the feed mapping page would show all available sources, not just the sources allowed for the field. ([#1234](https://github.com/craftcms/feed-me/pull/1234))
- Fixed a PHP error that could occur when a feed contained an empty value that was mapped to an Assets field. ([#1229](https://github.com/craftcms/feed-me/pull/1229), [#1195](https://github.com/craftcms/feed-me/issues/1195), [#1106](https://github.com/craftcms/feed-me/issues/1106), [#1154](https://github.com/craftcms/feed-me/issues/1154))
- Fixed a bug where arrays could be misinterpreted during feed imports. ([#1236](https://github.com/craftcms/feed-me/pull/1236), [#1237](https://github.com/craftcms/feed-me/pull/1237/), [#1238](https://github.com/craftcms/feed-me/issues/1238))
- Fixed several issues related to importing categories and Structure section entries. ([#1240](https://github.com/craftcms/feed-me/pull/1240), [#1154](https://github.com/craftcms/feed-me/issues/1154))
- Fixed a PHP error that could occur when importing relational field data within a Matrix field. ([#1069](https://github.com/craftcms/feed-me/issues/1069))
- Fixed a PHP error that occurred when importing an asset with a filename over 255 characters long.
- Fixed a PHP error that occurred if an Entries field was configured to use a custom source. ([#1186](https://github.com/craftcms/feed-me/issues/1186))
- Fixed a bug where importing into Matrix fields that had identically-named sub-fields across block types would only import to the first matching field. ([#1185](https://github.com/craftcms/feed-me/pull/1185), [#1226](https://github.com/craftcms/feed-me/issues/1226), [#1184](https://github.com/craftcms/feed-me/issues/1184))
- Fixed a compatibility issue with Google Maps 4.3+. ([#1245](https://github.com/craftcms/feed-me/pull/1245))

## 5.0.5 - 2023-01-09

### Fixed
- Fixed a bug where `enabledForSite` was still being used in element selector modal criteria. ([#1126](https://github.com/craftcms/feed-me/issues/1126))
- Fixed a bug where a user’s status wouldn’t be set to “Active” if the feed specified so. ([#1182](https://github.com/craftcms/feed-me/issues/1182))
- Fixed a bug where sites in a Site Group would all have their statuses updated when a feed was targeting a single site. ([#1208](https://github.com/craftcms/feed-me/issues/1208))
- Fixed importing using the LinkIt plugin. ([#1203](https://github.com/craftcms/feed-me/issues/1203))
- Fixed a bug where some element custom fields would not display when setting up feed mappings. ([#1209](https://github.com/craftcms/feed-me/pull/1209))

## 5.0.4 - 2022-05-24

### Fixed
- Fixed a PHP error that could occur when importing a base64-encoded asset.
- Fixed a bug where asset file names were getting normalized before searching for an existing asset when the feed specified a file path. ([#847](https://github.com/craftcms/feed-me/issues/847))

## 5.0.3 - 2022-05-17

### Fixed
- Fixed CSV importing. ([#1137](https://github.com/craftcms/feed-me/pull/1137)) 

### Changed
- The `EVENT_AFTER_PARSE_FEED` event now passes in the feed’s ID. ([#1107](https://github.com/craftcms/feed-me/issues/1107))

## 5.0.2 - 2022-05-11

### Fixed
- Fixed various PHP errors. ([#1132](https://github.com/craftcms/feed-me/issues/1132), [#1133](https://github.com/craftcms/feed-me/issues/1133))

## 5.0.1 - 2022-05-05

### Fixed
- Fixed a bug where elements’ per-site statuses weren’t getting set for feeds that specified a status. ([#822](https://github.com/craftcms/feed-me/issues/822))
- Fixed various PHP errors. ([#1128](https://github.com/craftcms/feed-me/issues/1128), [#1130](https://github.com/craftcms/feed-me/issues/1130), [#1131](https://github.com/craftcms/feed-me/issues/1131))

## 5.0.0 - 2022-05-03

### Added
- Added Craft 4 compatibility.

### Changed
- The `data`, `elements`, `feeds`, `fields`, `logs`, `process`, and `service` components can now be configured via `craft\services\Plugins::$pluginConfigs`.

### Removed
- Removed built-in support for the Verbb Comments plugin, which provides its own Feed Me driver.
