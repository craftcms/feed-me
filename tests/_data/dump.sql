# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: localhost (MySQL 5.6.38)
# Database: craft3_tests_base
# Generation Time: 2018-04-03 14:26:46 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table assetindexdata
# ------------------------------------------------------------

DROP TABLE IF EXISTS `assetindexdata`;

CREATE TABLE `assetindexdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(36) NOT NULL DEFAULT '',
  `volumeId` int(11) NOT NULL,
  `uri` text,
  `size` bigint(20) unsigned DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `recordId` int(11) DEFAULT NULL,
  `inProgress` tinyint(1) DEFAULT '0',
  `completed` tinyint(1) DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `assetindexdata_sessionId_volumeId_idx` (`sessionId`,`volumeId`),
  KEY `assetindexdata_volumeId_idx` (`volumeId`),
  CONSTRAINT `assetindexdata_volumeId_fk` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table assets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `assets`;

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `volumeId` int(11) DEFAULT NULL,
  `folderId` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `kind` varchar(50) NOT NULL DEFAULT 'unknown',
  `width` int(11) unsigned DEFAULT NULL,
  `height` int(11) unsigned DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  `focalPoint` varchar(13) DEFAULT NULL,
  `dateModified` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `assets_filename_folderId_unq_idx` (`filename`,`folderId`),
  KEY `assets_folderId_idx` (`folderId`),
  KEY `assets_volumeId_idx` (`volumeId`),
  CONSTRAINT `assets_folderId_fk` FOREIGN KEY (`folderId`) REFERENCES `volumefolders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assets_volumeId_fk` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table assettransformindex
# ------------------------------------------------------------

DROP TABLE IF EXISTS `assettransformindex`;

CREATE TABLE `assettransformindex` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assetId` int(11) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `format` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `volumeId` int(11) DEFAULT NULL,
  `fileExists` tinyint(1) NOT NULL DEFAULT '0',
  `inProgress` tinyint(1) NOT NULL DEFAULT '0',
  `dateIndexed` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `assettransformindex_volumeId_assetId_location_idx` (`volumeId`,`assetId`,`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table assettransforms
# ------------------------------------------------------------

DROP TABLE IF EXISTS `assettransforms`;

CREATE TABLE `assettransforms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `mode` enum('stretch','fit','crop') NOT NULL DEFAULT 'crop',
  `position` enum('top-left','top-center','top-right','center-left','center-center','center-right','bottom-left','bottom-center','bottom-right') NOT NULL DEFAULT 'center-center',
  `width` int(11) unsigned DEFAULT NULL,
  `height` int(11) unsigned DEFAULT NULL,
  `format` varchar(255) DEFAULT NULL,
  `quality` int(11) DEFAULT NULL,
  `interlace` enum('none','line','plane','partition') NOT NULL DEFAULT 'none',
  `dimensionChangeTime` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `assettransforms_name_unq_idx` (`name`),
  UNIQUE KEY `assettransforms_handle_unq_idx` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `categories_groupId_idx` (`groupId`),
  CONSTRAINT `categories_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `categorygroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categories_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table categorygroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categorygroups`;

CREATE TABLE `categorygroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `structureId` int(11) NOT NULL,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorygroups_name_unq_idx` (`name`),
  UNIQUE KEY `categorygroups_handle_unq_idx` (`handle`),
  KEY `categorygroups_structureId_idx` (`structureId`),
  KEY `categorygroups_fieldLayoutId_idx` (`fieldLayoutId`),
  CONSTRAINT `categorygroups_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `categorygroups_structureId_fk` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table categorygroups_sites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categorygroups_sites`;

CREATE TABLE `categorygroups_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '1',
  `uriFormat` text,
  `template` varchar(500) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categorygroups_sites_groupId_siteId_unq_idx` (`groupId`,`siteId`),
  KEY `categorygroups_sites_siteId_idx` (`siteId`),
  CONSTRAINT `categorygroups_sites_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `categorygroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `categorygroups_sites_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table content
# ------------------------------------------------------------

DROP TABLE IF EXISTS `content`;

CREATE TABLE `content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `elementId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_elementId_siteId_unq_idx` (`elementId`,`siteId`),
  KEY `content_siteId_idx` (`siteId`),
  KEY `content_title_idx` (`title`),
  CONSTRAINT `content_elementId_fk` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `content_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `content` WRITE;
/*!40000 ALTER TABLE `content` DISABLE KEYS */;

INSERT INTO `content` (`id`, `elementId`, `siteId`, `title`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,1,1,NULL,'2018-04-03 14:25:33','2018-04-03 14:25:33','e1b100eb-c9b8-466f-a1a1-d0c41a18ed45');

/*!40000 ALTER TABLE `content` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table craftidtokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `craftidtokens`;

CREATE TABLE `craftidtokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `accessToken` text NOT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `craftidtokens_userId_fk` (`userId`),
  CONSTRAINT `craftidtokens_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table deprecationerrors
# ------------------------------------------------------------

DROP TABLE IF EXISTS `deprecationerrors`;

CREATE TABLE `deprecationerrors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `fingerprint` varchar(255) NOT NULL,
  `lastOccurrence` datetime NOT NULL,
  `file` varchar(255) NOT NULL,
  `line` smallint(6) unsigned DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `traces` text,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `deprecationerrors_key_fingerprint_unq_idx` (`key`,`fingerprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table elementindexsettings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `elementindexsettings`;

CREATE TABLE `elementindexsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `settings` text,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `elementindexsettings_type_unq_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table elements
# ------------------------------------------------------------

DROP TABLE IF EXISTS `elements`;

CREATE TABLE `elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `archived` tinyint(1) NOT NULL DEFAULT '0',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `elements_fieldLayoutId_idx` (`fieldLayoutId`),
  KEY `elements_type_idx` (`type`),
  KEY `elements_enabled_idx` (`enabled`),
  KEY `elements_archived_dateCreated_idx` (`archived`,`dateCreated`),
  CONSTRAINT `elements_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `elements` WRITE;
/*!40000 ALTER TABLE `elements` DISABLE KEYS */;

INSERT INTO `elements` (`id`, `fieldLayoutId`, `type`, `enabled`, `archived`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,NULL,'craft\\elements\\User',1,0,'2018-04-03 14:25:33','2018-04-03 14:25:33','a5db76a2-9c7e-415b-ad9c-042ccfa4c13b');

/*!40000 ALTER TABLE `elements` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table elements_sites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `elements_sites`;

CREATE TABLE `elements_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `elementId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `elements_sites_elementId_siteId_unq_idx` (`elementId`,`siteId`),
  UNIQUE KEY `elements_sites_uri_siteId_unq_idx` (`uri`,`siteId`),
  KEY `elements_sites_siteId_idx` (`siteId`),
  KEY `elements_sites_slug_siteId_idx` (`slug`,`siteId`),
  KEY `elements_sites_enabled_idx` (`enabled`),
  CONSTRAINT `elements_sites_elementId_fk` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `elements_sites_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `elements_sites` WRITE;
/*!40000 ALTER TABLE `elements_sites` DISABLE KEYS */;

INSERT INTO `elements_sites` (`id`, `elementId`, `siteId`, `slug`, `uri`, `enabled`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,1,1,NULL,NULL,1,'2018-04-03 14:25:33','2018-04-03 14:25:33','a1b31cc5-034f-4758-bc27-5ef99bde80e5');

/*!40000 ALTER TABLE `elements_sites` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table entries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `entries`;

CREATE TABLE `entries` (
  `id` int(11) NOT NULL,
  `sectionId` int(11) NOT NULL,
  `typeId` int(11) NOT NULL,
  `authorId` int(11) DEFAULT NULL,
  `postDate` datetime DEFAULT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entries_postDate_idx` (`postDate`),
  KEY `entries_expiryDate_idx` (`expiryDate`),
  KEY `entries_authorId_idx` (`authorId`),
  KEY `entries_sectionId_idx` (`sectionId`),
  KEY `entries_typeId_idx` (`typeId`),
  CONSTRAINT `entries_authorId_fk` FOREIGN KEY (`authorId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entries_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entries_sectionId_fk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entries_typeId_fk` FOREIGN KEY (`typeId`) REFERENCES `entrytypes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table entrydrafts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `entrydrafts`;

CREATE TABLE `entrydrafts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryId` int(11) NOT NULL,
  `sectionId` int(11) NOT NULL,
  `creatorId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `notes` text,
  `data` mediumtext NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entrydrafts_sectionId_idx` (`sectionId`),
  KEY `entrydrafts_entryId_siteId_idx` (`entryId`,`siteId`),
  KEY `entrydrafts_siteId_idx` (`siteId`),
  KEY `entrydrafts_creatorId_idx` (`creatorId`),
  CONSTRAINT `entrydrafts_creatorId_fk` FOREIGN KEY (`creatorId`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrydrafts_entryId_fk` FOREIGN KEY (`entryId`) REFERENCES `entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrydrafts_sectionId_fk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entrydrafts_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table entrytypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `entrytypes`;

CREATE TABLE `entrytypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sectionId` int(11) NOT NULL,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `hasTitleField` tinyint(1) NOT NULL DEFAULT '1',
  `titleLabel` varchar(255) DEFAULT 'Title',
  `titleFormat` varchar(255) DEFAULT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entrytypes_name_sectionId_unq_idx` (`name`,`sectionId`),
  UNIQUE KEY `entrytypes_handle_sectionId_unq_idx` (`handle`,`sectionId`),
  KEY `entrytypes_sectionId_idx` (`sectionId`),
  KEY `entrytypes_fieldLayoutId_idx` (`fieldLayoutId`),
  CONSTRAINT `entrytypes_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entrytypes_sectionId_fk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table entryversions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `entryversions`;

CREATE TABLE `entryversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entryId` int(11) NOT NULL,
  `sectionId` int(11) NOT NULL,
  `creatorId` int(11) DEFAULT NULL,
  `siteId` int(11) NOT NULL,
  `num` smallint(6) unsigned NOT NULL,
  `notes` text,
  `data` mediumtext NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `entryversions_sectionId_idx` (`sectionId`),
  KEY `entryversions_entryId_siteId_idx` (`entryId`,`siteId`),
  KEY `entryversions_siteId_idx` (`siteId`),
  KEY `entryversions_creatorId_idx` (`creatorId`),
  CONSTRAINT `entryversions_creatorId_fk` FOREIGN KEY (`creatorId`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entryversions_entryId_fk` FOREIGN KEY (`entryId`) REFERENCES `entries` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entryversions_sectionId_fk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entryversions_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table fieldgroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fieldgroups`;

CREATE TABLE `fieldgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldgroups_name_unq_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `fieldgroups` WRITE;
/*!40000 ALTER TABLE `fieldgroups` DISABLE KEYS */;

INSERT INTO `fieldgroups` (`id`, `name`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'Common','2018-04-03 14:25:33','2018-04-03 14:25:33','4e1b4923-5e4b-408e-99de-040b87627863');

/*!40000 ALTER TABLE `fieldgroups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table fieldlayoutfields
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fieldlayoutfields`;

CREATE TABLE `fieldlayoutfields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layoutId` int(11) NOT NULL,
  `tabId` int(11) NOT NULL,
  `fieldId` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fieldlayoutfields_layoutId_fieldId_unq_idx` (`layoutId`,`fieldId`),
  KEY `fieldlayoutfields_sortOrder_idx` (`sortOrder`),
  KEY `fieldlayoutfields_tabId_idx` (`tabId`),
  KEY `fieldlayoutfields_fieldId_idx` (`fieldId`),
  CONSTRAINT `fieldlayoutfields_fieldId_fk` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fieldlayoutfields_layoutId_fk` FOREIGN KEY (`layoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fieldlayoutfields_tabId_fk` FOREIGN KEY (`tabId`) REFERENCES `fieldlayouttabs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table fieldlayouts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fieldlayouts`;

CREATE TABLE `fieldlayouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fieldlayouts_type_idx` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table fieldlayouttabs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fieldlayouttabs`;

CREATE TABLE `fieldlayouttabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `layoutId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fieldlayouttabs_sortOrder_idx` (`sortOrder`),
  KEY `fieldlayouttabs_layoutId_idx` (`layoutId`),
  CONSTRAINT `fieldlayouttabs_layoutId_fk` FOREIGN KEY (`layoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table fields
# ------------------------------------------------------------

DROP TABLE IF EXISTS `fields`;

CREATE TABLE `fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(64) NOT NULL,
  `context` varchar(255) NOT NULL DEFAULT 'global',
  `instructions` text,
  `translationMethod` varchar(255) NOT NULL DEFAULT 'none',
  `translationKeyFormat` text,
  `type` varchar(255) NOT NULL,
  `settings` text,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `fields_handle_context_unq_idx` (`handle`,`context`),
  KEY `fields_groupId_idx` (`groupId`),
  KEY `fields_context_idx` (`context`),
  CONSTRAINT `fields_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `fieldgroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table globalsets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `globalsets`;

CREATE TABLE `globalsets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `globalsets_name_unq_idx` (`name`),
  UNIQUE KEY `globalsets_handle_unq_idx` (`handle`),
  KEY `globalsets_fieldLayoutId_idx` (`fieldLayoutId`),
  CONSTRAINT `globalsets_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `globalsets_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table info
# ------------------------------------------------------------

DROP TABLE IF EXISTS `info`;

CREATE TABLE `info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `version` varchar(50) NOT NULL,
  `schemaVersion` varchar(15) NOT NULL,
  `edition` tinyint(3) unsigned NOT NULL,
  `timezone` varchar(30) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `on` tinyint(1) NOT NULL DEFAULT '0',
  `maintenance` tinyint(1) NOT NULL DEFAULT '0',
  `fieldVersion` char(12) NOT NULL DEFAULT '000000000000',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `info` WRITE;
/*!40000 ALTER TABLE `info` DISABLE KEYS */;

INSERT INTO `info` (`id`, `version`, `schemaVersion`, `edition`, `timezone`, `name`, `on`, `maintenance`, `fieldVersion`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'3.0.0-RC17.1','3.0.85',0,'America/Los_Angeles','Craft3 Tests',1,0,'L95NH9ee9bhm','2018-04-03 14:25:32','2018-04-03 14:25:32','ece6114e-29ce-46f3-98fa-4a56cabd677f');

/*!40000 ALTER TABLE `info` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table matrixblocks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matrixblocks`;

CREATE TABLE `matrixblocks` (
  `id` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `ownerSiteId` int(11) DEFAULT NULL,
  `fieldId` int(11) NOT NULL,
  `typeId` int(11) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `matrixblocks_ownerId_idx` (`ownerId`),
  KEY `matrixblocks_fieldId_idx` (`fieldId`),
  KEY `matrixblocks_typeId_idx` (`typeId`),
  KEY `matrixblocks_sortOrder_idx` (`sortOrder`),
  KEY `matrixblocks_ownerSiteId_idx` (`ownerSiteId`),
  CONSTRAINT `matrixblocks_fieldId_fk` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matrixblocks_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matrixblocks_ownerId_fk` FOREIGN KEY (`ownerId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matrixblocks_ownerSiteId_fk` FOREIGN KEY (`ownerSiteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `matrixblocks_typeId_fk` FOREIGN KEY (`typeId`) REFERENCES `matrixblocktypes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table matrixblocktypes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `matrixblocktypes`;

CREATE TABLE `matrixblocktypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldId` int(11) NOT NULL,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `matrixblocktypes_name_fieldId_unq_idx` (`name`,`fieldId`),
  UNIQUE KEY `matrixblocktypes_handle_fieldId_unq_idx` (`handle`,`fieldId`),
  KEY `matrixblocktypes_fieldId_idx` (`fieldId`),
  KEY `matrixblocktypes_fieldLayoutId_idx` (`fieldLayoutId`),
  CONSTRAINT `matrixblocktypes_fieldId_fk` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matrixblocktypes_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table migrations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginId` int(11) DEFAULT NULL,
  `type` enum('app','plugin','content') NOT NULL DEFAULT 'app',
  `name` varchar(255) NOT NULL,
  `applyTime` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `migrations_pluginId_idx` (`pluginId`),
  KEY `migrations_type_pluginId_idx` (`type`,`pluginId`),
  CONSTRAINT `migrations_pluginId_fk` FOREIGN KEY (`pluginId`) REFERENCES `plugins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;

INSERT INTO `migrations` (`id`, `pluginId`, `type`, `name`, `applyTime`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,NULL,'app','Install','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','3fe13e6e-da69-430a-b278-1f6f3da65a65'),
	(2,NULL,'app','m150403_183908_migrations_table_changes','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','bc1e20a4-6d66-40e1-be66-de5180af92f1'),
	(3,NULL,'app','m150403_184247_plugins_table_changes','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','b0610781-2170-4823-ac15-483c5e7b9ced'),
	(4,NULL,'app','m150403_184533_field_version','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','c13a6785-51d9-4914-b278-91742c2caf98'),
	(5,NULL,'app','m150403_184729_type_columns','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','2a9ca708-2a18-4dea-bbbc-f723b74a2731'),
	(6,NULL,'app','m150403_185142_volumes','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','15fdedee-1026-43ef-ac64-f416ee3d5317'),
	(7,NULL,'app','m150428_231346_userpreferences','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','d110ea84-0472-4632-beda-1174a189ab30'),
	(8,NULL,'app','m150519_150900_fieldversion_conversion','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','b3f22013-bc7f-4562-b79f-0f30a0c86437'),
	(9,NULL,'app','m150617_213829_update_email_settings','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','a2b3d783-5a39-4593-b926-9ff7f36a0109'),
	(10,NULL,'app','m150721_124739_templatecachequeries','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','63ca2e9c-9b86-4cc9-9dba-c48a20e2fa00'),
	(11,NULL,'app','m150724_140822_adjust_quality_settings','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','11c2f79a-ad96-40bf-91e9-b1402a99d568'),
	(12,NULL,'app','m150815_133521_last_login_attempt_ip','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','33bdceb4-d2f9-4105-a31f-da38e817e83e'),
	(13,NULL,'app','m151002_095935_volume_cache_settings','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','0e4ea022-ee0a-4841-bbd6-f3467f94ff25'),
	(14,NULL,'app','m151005_142750_volume_s3_storage_settings','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','9bfc2805-c6dd-408e-ae11-e4702c86f683'),
	(15,NULL,'app','m151016_133600_delete_asset_thumbnails','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','8cceb032-0559-4a51-965f-6f9f3f8baaf5'),
	(16,NULL,'app','m151209_000000_move_logo','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','36e26537-0a4a-4ef6-87a6-d3bc44b00e95'),
	(17,NULL,'app','m151211_000000_rename_fileId_to_assetId','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','22ab06d1-c11d-4f04-8256-6a6113503442'),
	(18,NULL,'app','m151215_000000_rename_asset_permissions','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','4dda0058-ddc1-450a-b82b-ab9d5e9e2b8a'),
	(19,NULL,'app','m160707_000001_rename_richtext_assetsource_setting','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','2bf00e2f-7d4e-466b-8e10-5209fe8dcf6b'),
	(20,NULL,'app','m160708_185142_volume_hasUrls_setting','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','c6959161-037b-4461-8656-d9d5c6df3c4c'),
	(21,NULL,'app','m160714_000000_increase_max_asset_filesize','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','ed77181a-708c-4666-ac2e-a4f457c02ba8'),
	(22,NULL,'app','m160727_194637_column_cleanup','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','3d72ebee-5328-4a2e-bf67-22e52e3f2da9'),
	(23,NULL,'app','m160804_110002_userphotos_to_assets','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','b5846d54-b265-463f-a4e9-03b7958e69f3'),
	(24,NULL,'app','m160807_144858_sites','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','e100bcf9-0310-438c-ab6e-8a3be0527ff7'),
	(25,NULL,'app','m160829_000000_pending_user_content_cleanup','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','401bd1a5-ae01-4e1f-9da3-93d86bfb725a'),
	(26,NULL,'app','m160830_000000_asset_index_uri_increase','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','46bde7fa-f51e-463b-8af1-c01bf5d4a53b'),
	(27,NULL,'app','m160912_230520_require_entry_type_id','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','d3574e3b-f37e-4004-9bdc-2a64b55f6fe3'),
	(28,NULL,'app','m160913_134730_require_matrix_block_type_id','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','5ba5c2ff-0f16-4891-bffd-8d61bed30768'),
	(29,NULL,'app','m160920_174553_matrixblocks_owner_site_id_nullable','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','ecc8f271-f6d7-4879-967f-479c29168b9b'),
	(30,NULL,'app','m160920_231045_usergroup_handle_title_unique','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','7f4035b0-4048-45f1-b78a-990945f3aeb9'),
	(31,NULL,'app','m160925_113941_route_uri_parts','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','a4a9c4c1-61d2-4a26-ba64-0e5e4a7728de'),
	(32,NULL,'app','m161006_205918_schemaVersion_not_null','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','ac07ec4b-8efe-48ae-bff7-f7f58d069fd8'),
	(33,NULL,'app','m161007_130653_update_email_settings','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','9bc68571-d572-4f2a-a37f-342e0aae9f48'),
	(34,NULL,'app','m161013_175052_newParentId','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','b72da037-55de-4c26-a15a-ebc0e886a212'),
	(35,NULL,'app','m161021_102916_fix_recent_entries_widgets','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','3ad3a9b6-96ad-4ead-b72e-d3e3a71175b8'),
	(36,NULL,'app','m161021_182140_rename_get_help_widget','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','a04f47e9-f19e-4a77-acc3-6a76aa3d7a2c'),
	(37,NULL,'app','m161025_000000_fix_char_columns','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','a8ce3584-af14-453e-8830-d96c96c7f08d'),
	(38,NULL,'app','m161029_124145_email_message_languages','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','f47ec0a4-2a90-400c-91b9-b748096a8006'),
	(39,NULL,'app','m161108_000000_new_version_format','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','8063aada-0577-4c15-8408-311de96d86c3'),
	(40,NULL,'app','m161109_000000_index_shuffle','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','fb4e7325-8b37-4b5f-8684-76a50b3d0fe4'),
	(41,NULL,'app','m161122_185500_no_craft_app','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','ef528085-b1f0-4a77-93f1-aa4e2bac9435'),
	(42,NULL,'app','m161125_150752_clear_urlmanager_cache','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','57d366ce-3ff5-48d6-ba49-960e6dbb8303'),
	(43,NULL,'app','m161220_000000_volumes_hasurl_notnull','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','f20cd9ae-63af-4352-811a-6f821c6aef3b'),
	(44,NULL,'app','m170114_161144_udates_permission','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','c10e9879-89d3-4e0b-94e2-7acd169a3795'),
	(45,NULL,'app','m170120_000000_schema_cleanup','2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','4ab709a0-a33c-49b8-80f2-7070361c28fe'),
	(46,NULL,'app','m170126_000000_assets_focal_point','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','ffc799df-727d-468c-8854-b1035dd8cbf5'),
	(47,NULL,'app','m170206_142126_system_name','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','3eb3a690-c788-4b1a-994f-636418472a1e'),
	(48,NULL,'app','m170217_044740_category_branch_limits','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','a4f3bd65-76d2-4d4b-9b37-518e217b4f1e'),
	(49,NULL,'app','m170217_120224_asset_indexing_columns','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','09630939-9295-47c8-84a9-0d99e48cda87'),
	(50,NULL,'app','m170223_224012_plain_text_settings','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','fe355be0-b8d1-43c8-b624-13a839ef8925'),
	(51,NULL,'app','m170227_120814_focal_point_percentage','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','28b6e86f-dfc1-42cb-9a3f-be31e56b0f46'),
	(52,NULL,'app','m170228_171113_system_messages','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','3bd33540-5368-4e9e-8ffd-44893d42b46d'),
	(53,NULL,'app','m170303_140500_asset_field_source_settings','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','c7087494-7de3-4192-91ac-6d02f7b9e0a4'),
	(54,NULL,'app','m170306_150500_asset_temporary_uploads','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','a25e7b4c-d87c-40d3-9a1d-838f5c4f278b'),
	(55,NULL,'app','m170414_162429_rich_text_config_setting','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','d3e2f214-0875-4e8f-a78e-c029566a0d23'),
	(56,NULL,'app','m170523_190652_element_field_layout_ids','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','37f8b29a-4d05-436c-b984-3e582269e23c'),
	(57,NULL,'app','m170612_000000_route_index_shuffle','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','f0acf7c7-935d-4abd-92e5-9d0f66cbf9ce'),
	(58,NULL,'app','m170621_195237_format_plugin_handles','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','69bee6cc-7eb6-41ac-9ebc-3fa5b3d3a3e1'),
	(59,NULL,'app','m170630_161028_deprecation_changes','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','eaf71c92-a5c5-442f-b303-1f222da6cb42'),
	(60,NULL,'app','m170703_181539_plugins_table_tweaks','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','2cfbbce7-0f53-4efa-a716-8fb2de8bbfd9'),
	(61,NULL,'app','m170704_134916_sites_tables','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','23fea68f-f72a-4e32-b919-c642c490beac'),
	(62,NULL,'app','m170706_183216_rename_sequences','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','18f5c5a3-c94e-4001-ab0b-859e186336c9'),
	(63,NULL,'app','m170707_094758_delete_compiled_traits','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','be1bc41b-8192-40b1-9b27-f912c03a6413'),
	(64,NULL,'app','m170731_190138_drop_asset_packagist','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','9e112538-cd5e-4838-b085-1a233f486633'),
	(65,NULL,'app','m170810_201318_create_queue_table','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','11ba5d82-7935-4b28-b75d-732ce02b8a34'),
	(66,NULL,'app','m170816_133741_delete_compiled_behaviors','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','3c76647c-0d6c-40d9-8cb3-75592e1659f7'),
	(67,NULL,'app','m170821_180624_deprecation_line_nullable','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','5f9826e4-6deb-48f4-ad90-07bad9ceb670'),
	(68,NULL,'app','m170903_192801_longblob_for_queue_jobs','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','eb07335c-8522-4b2a-9dfb-316b064a86f5'),
	(69,NULL,'app','m170914_204621_asset_cache_shuffle','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','7a1c23be-81eb-4261-bb26-8ce63d849c14'),
	(70,NULL,'app','m171011_214115_site_groups','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','ce43e722-bfa4-445f-aa42-cb88d19c952e'),
	(71,NULL,'app','m171012_151440_primary_site','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','fd1cee20-4383-4fa6-bb08-95cd6a5eae0f'),
	(72,NULL,'app','m171013_142500_transform_interlace','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','8dca87d9-6064-47c0-9eb2-3ecf761101b5'),
	(73,NULL,'app','m171016_092553_drop_position_select','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','c20d875b-7050-4005-911e-ea0185d77cb4'),
	(74,NULL,'app','m171016_221244_less_strict_translation_method','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','15f4df87-a217-4615-a062-db419632421c'),
	(75,NULL,'app','m171107_000000_assign_group_permissions','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','3d2f6ef2-b13a-4232-aaf0-8f2c2412a569'),
	(76,NULL,'app','m171117_000001_templatecache_index_tune','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','452ed1bd-7c3e-4de1-b778-477dbe6c9fa8'),
	(77,NULL,'app','m171126_105927_disabled_plugins','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','68fbb48d-40e8-44ab-b890-8a94da97f251'),
	(78,NULL,'app','m171130_214407_craftidtokens_table','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','a8e2799d-d632-4640-b501-e9645b324795'),
	(79,NULL,'app','m171202_004225_update_email_settings','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','2034ef1c-bbc1-483b-ab63-49cd883404a4'),
	(80,NULL,'app','m171204_000001_templatecache_index_tune_deux','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','94dec31c-8c40-43c8-9659-bf0c57e1d517'),
	(81,NULL,'app','m171205_130908_remove_craftidtokens_refreshtoken_column','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','d3b7ac0c-b303-489c-94c5-de2a15df0517'),
	(82,NULL,'app','m171210_142046_fix_db_routes','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','1301195c-f04d-4a37-bc19-5c088c4a59a8'),
	(83,NULL,'app','m171218_143135_longtext_query_column','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','48858112-a622-4037-988f-3e75a9cd70e8'),
	(84,NULL,'app','m171231_055546_environment_variables_to_aliases','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','4c691707-a7b1-4fe1-bcad-61c08195a069'),
	(85,NULL,'app','m180113_153740_drop_users_archived_column','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','7eda8632-4b7e-49c2-a064-347c6175e152'),
	(86,NULL,'app','m180122_213433_propagate_entries_setting','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','00355930-3ed6-4741-ac46-62d3741e3454'),
	(87,NULL,'app','m180124_230459_fix_propagate_entries_values','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','e71c9e4c-3f35-4869-b8e6-085f843f6341'),
	(88,NULL,'app','m180128_235202_set_tag_slugs','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','41f127a7-54ab-4468-b49a-4db7d394d7d4'),
	(89,NULL,'app','m180202_185551_fix_focal_points','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','e49b653a-409e-4c2a-abbd-66124f278baa'),
	(90,NULL,'app','m180217_172123_tiny_ints','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','e78a664d-c6c2-4190-9083-7fadda8d0d8b'),
	(91,NULL,'app','m180321_233505_small_ints','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','5b35421e-7cf3-47dc-a622-f70b97131a16'),
	(92,NULL,'app','m180328_115523_new_license_key_statuses','2018-04-03 14:25:35','2018-04-03 14:25:35','2018-04-03 14:25:35','988d6a1f-7353-44b2-984a-ac79447b6072');

/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table plugins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `plugins`;

CREATE TABLE `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `handle` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `schemaVersion` varchar(255) NOT NULL,
  `licenseKey` char(24) DEFAULT NULL,
  `licenseKeyStatus` enum('valid','invalid','mismatched','astray','unknown') NOT NULL DEFAULT 'unknown',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `settings` text,
  `installDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugins_handle_unq_idx` (`handle`),
  KEY `plugins_enabled_idx` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table queue
# ------------------------------------------------------------

DROP TABLE IF EXISTS `queue`;

CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job` longblob NOT NULL,
  `description` text,
  `timePushed` int(11) NOT NULL,
  `ttr` int(11) NOT NULL,
  `delay` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) unsigned NOT NULL DEFAULT '1024',
  `dateReserved` datetime DEFAULT NULL,
  `timeUpdated` int(11) DEFAULT NULL,
  `progress` smallint(6) NOT NULL DEFAULT '0',
  `attempt` int(11) DEFAULT NULL,
  `fail` tinyint(1) DEFAULT '0',
  `dateFailed` datetime DEFAULT NULL,
  `error` text,
  PRIMARY KEY (`id`),
  KEY `queue_fail_timeUpdated_timePushed_idx` (`fail`,`timeUpdated`,`timePushed`),
  KEY `queue_fail_timeUpdated_delay_idx` (`fail`,`timeUpdated`,`delay`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table relations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `relations`;

CREATE TABLE `relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldId` int(11) NOT NULL,
  `sourceId` int(11) NOT NULL,
  `sourceSiteId` int(11) DEFAULT NULL,
  `targetId` int(11) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `relations_fieldId_sourceId_sourceSiteId_targetId_unq_idx` (`fieldId`,`sourceId`,`sourceSiteId`,`targetId`),
  KEY `relations_sourceId_idx` (`sourceId`),
  KEY `relations_targetId_idx` (`targetId`),
  KEY `relations_sourceSiteId_idx` (`sourceSiteId`),
  CONSTRAINT `relations_fieldId_fk` FOREIGN KEY (`fieldId`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `relations_sourceId_fk` FOREIGN KEY (`sourceId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `relations_sourceSiteId_fk` FOREIGN KEY (`sourceSiteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `relations_targetId_fk` FOREIGN KEY (`targetId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table routes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `routes`;

CREATE TABLE `routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteId` int(11) DEFAULT NULL,
  `uriParts` varchar(255) NOT NULL,
  `uriPattern` varchar(255) NOT NULL,
  `template` varchar(500) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `routes_uriPattern_idx` (`uriPattern`),
  KEY `routes_siteId_idx` (`siteId`),
  CONSTRAINT `routes_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table searchindex
# ------------------------------------------------------------

DROP TABLE IF EXISTS `searchindex`;

CREATE TABLE `searchindex` (
  `elementId` int(11) NOT NULL,
  `attribute` varchar(25) NOT NULL,
  `fieldId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `keywords` text NOT NULL,
  PRIMARY KEY (`elementId`,`attribute`,`fieldId`,`siteId`),
  FULLTEXT KEY `searchindex_keywords_idx` (`keywords`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `searchindex` WRITE;
/*!40000 ALTER TABLE `searchindex` DISABLE KEYS */;

INSERT INTO `searchindex` (`elementId`, `attribute`, `fieldId`, `siteId`, `keywords`)
VALUES
	(1,'username',0,1,' admin '),
	(1,'firstname',0,1,''),
	(1,'lastname',0,1,''),
	(1,'fullname',0,1,''),
	(1,'email',0,1,' web sgroup com au '),
	(1,'slug',0,1,'');

/*!40000 ALTER TABLE `searchindex` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sections
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sections`;

CREATE TABLE `sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `structureId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `type` enum('single','channel','structure') NOT NULL DEFAULT 'channel',
  `enableVersioning` tinyint(1) NOT NULL DEFAULT '0',
  `propagateEntries` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_handle_unq_idx` (`handle`),
  UNIQUE KEY `sections_name_unq_idx` (`name`),
  KEY `sections_structureId_idx` (`structureId`),
  CONSTRAINT `sections_structureId_fk` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sections_sites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sections_sites`;

CREATE TABLE `sections_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sectionId` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '1',
  `uriFormat` text,
  `template` varchar(500) DEFAULT NULL,
  `enabledByDefault` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sections_sites_sectionId_siteId_unq_idx` (`sectionId`,`siteId`),
  KEY `sections_sites_siteId_idx` (`siteId`),
  CONSTRAINT `sections_sites_sectionId_fk` FOREIGN KEY (`sectionId`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sections_sites_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `token` char(100) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sessions_uid_idx` (`uid`),
  KEY `sessions_token_idx` (`token`),
  KEY `sessions_dateUpdated_idx` (`dateUpdated`),
  KEY `sessions_userId_idx` (`userId`),
  CONSTRAINT `sessions_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table shunnedmessages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `shunnedmessages`;

CREATE TABLE `shunnedmessages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `expiryDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `shunnedmessages_userId_message_unq_idx` (`userId`,`message`),
  CONSTRAINT `shunnedmessages_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table sitegroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sitegroups`;

CREATE TABLE `sitegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sitegroups_name_unq_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `sitegroups` WRITE;
/*!40000 ALTER TABLE `sitegroups` DISABLE KEYS */;

INSERT INTO `sitegroups` (`id`, `name`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'Craft3 Tests','2018-04-03 14:25:33','2018-04-03 14:25:33','a3865f39-c0e2-4ba7-bd2b-991222faa15a');

/*!40000 ALTER TABLE `sitegroups` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table sites
# ------------------------------------------------------------

DROP TABLE IF EXISTS `sites`;

CREATE TABLE `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `primary` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `language` varchar(12) NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '0',
  `baseUrl` varchar(255) DEFAULT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sites_handle_unq_idx` (`handle`),
  KEY `sites_sortOrder_idx` (`sortOrder`),
  KEY `sites_groupId_fk` (`groupId`),
  CONSTRAINT `sites_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `sitegroups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;

INSERT INTO `sites` (`id`, `groupId`, `primary`, `name`, `handle`, `language`, `hasUrls`, `baseUrl`, `sortOrder`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,1,1,'Craft3 Tests','default','en-AU',1,'@web/',1,'2018-04-03 14:25:33','2018-04-03 14:25:33','e8898ffa-6bb6-4c47-84ba-1180eac890d1');

/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table structureelements
# ------------------------------------------------------------

DROP TABLE IF EXISTS `structureelements`;

CREATE TABLE `structureelements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `structureId` int(11) NOT NULL,
  `elementId` int(11) DEFAULT NULL,
  `root` int(11) unsigned DEFAULT NULL,
  `lft` int(11) unsigned NOT NULL,
  `rgt` int(11) unsigned NOT NULL,
  `level` smallint(6) unsigned NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `structureelements_structureId_elementId_unq_idx` (`structureId`,`elementId`),
  KEY `structureelements_root_idx` (`root`),
  KEY `structureelements_lft_idx` (`lft`),
  KEY `structureelements_rgt_idx` (`rgt`),
  KEY `structureelements_level_idx` (`level`),
  KEY `structureelements_elementId_idx` (`elementId`),
  CONSTRAINT `structureelements_elementId_fk` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `structureelements_structureId_fk` FOREIGN KEY (`structureId`) REFERENCES `structures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table structures
# ------------------------------------------------------------

DROP TABLE IF EXISTS `structures`;

CREATE TABLE `structures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maxLevels` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table systemmessages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `systemmessages`;

CREATE TABLE `systemmessages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `subject` text NOT NULL,
  `body` text NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `systemmessages_key_language_unq_idx` (`key`,`language`),
  KEY `systemmessages_language_idx` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table systemsettings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `systemsettings`;

CREATE TABLE `systemsettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(15) NOT NULL,
  `settings` text,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `systemsettings_category_unq_idx` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `systemsettings` WRITE;
/*!40000 ALTER TABLE `systemsettings` DISABLE KEYS */;

INSERT INTO `systemsettings` (`id`, `category`, `settings`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'email','{\"fromEmail\":\"web@sgroup.com.au\",\"fromName\":\"Craft3 Tests\",\"transportType\":\"craft\\\\mail\\\\transportadapters\\\\Sendmail\"}','2018-04-03 14:25:34','2018-04-03 14:25:34','7f164cef-68e3-4ba7-97ce-6a214b573e00');

/*!40000 ALTER TABLE `systemsettings` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table taggroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `taggroups`;

CREATE TABLE `taggroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `taggroups_name_unq_idx` (`name`),
  UNIQUE KEY `taggroups_handle_unq_idx` (`handle`),
  KEY `taggroups_fieldLayoutId_fk` (`fieldLayoutId`),
  CONSTRAINT `taggroups_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `tags_groupId_idx` (`groupId`),
  CONSTRAINT `tags_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `taggroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tags_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table templatecacheelements
# ------------------------------------------------------------

DROP TABLE IF EXISTS `templatecacheelements`;

CREATE TABLE `templatecacheelements` (
  `cacheId` int(11) NOT NULL,
  `elementId` int(11) NOT NULL,
  KEY `templatecacheelements_cacheId_idx` (`cacheId`),
  KEY `templatecacheelements_elementId_idx` (`elementId`),
  CONSTRAINT `templatecacheelements_cacheId_fk` FOREIGN KEY (`cacheId`) REFERENCES `templatecaches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `templatecacheelements_elementId_fk` FOREIGN KEY (`elementId`) REFERENCES `elements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table templatecachequeries
# ------------------------------------------------------------

DROP TABLE IF EXISTS `templatecachequeries`;

CREATE TABLE `templatecachequeries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cacheId` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `query` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `templatecachequeries_cacheId_idx` (`cacheId`),
  KEY `templatecachequeries_type_idx` (`type`),
  CONSTRAINT `templatecachequeries_cacheId_fk` FOREIGN KEY (`cacheId`) REFERENCES `templatecaches` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table templatecaches
# ------------------------------------------------------------

DROP TABLE IF EXISTS `templatecaches`;

CREATE TABLE `templatecaches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteId` int(11) NOT NULL,
  `cacheKey` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `expiryDate` datetime NOT NULL,
  `body` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `templatecaches_cacheKey_siteId_expiryDate_path_idx` (`cacheKey`,`siteId`,`expiryDate`,`path`),
  KEY `templatecaches_cacheKey_siteId_expiryDate_idx` (`cacheKey`,`siteId`,`expiryDate`),
  KEY `templatecaches_siteId_idx` (`siteId`),
  CONSTRAINT `templatecaches_siteId_fk` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table tokens
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tokens`;

CREATE TABLE `tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` char(32) NOT NULL,
  `route` text,
  `usageLimit` tinyint(3) unsigned DEFAULT NULL,
  `usageCount` tinyint(3) unsigned DEFAULT NULL,
  `expiryDate` datetime NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tokens_token_unq_idx` (`token`),
  KEY `tokens_expiryDate_idx` (`expiryDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table usergroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `usergroups`;

CREATE TABLE `usergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usergroups_handle_unq_idx` (`handle`),
  UNIQUE KEY `usergroups_name_unq_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table usergroups_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `usergroups_users`;

CREATE TABLE `usergroups_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `usergroups_users_groupId_userId_unq_idx` (`groupId`,`userId`),
  KEY `usergroups_users_userId_idx` (`userId`),
  CONSTRAINT `usergroups_users_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `usergroups_users_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table userpermissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userpermissions`;

CREATE TABLE `userpermissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userpermissions_name_unq_idx` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table userpermissions_usergroups
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userpermissions_usergroups`;

CREATE TABLE `userpermissions_usergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permissionId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userpermissions_usergroups_permissionId_groupId_unq_idx` (`permissionId`,`groupId`),
  KEY `userpermissions_usergroups_groupId_idx` (`groupId`),
  CONSTRAINT `userpermissions_usergroups_groupId_fk` FOREIGN KEY (`groupId`) REFERENCES `usergroups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `userpermissions_usergroups_permissionId_fk` FOREIGN KEY (`permissionId`) REFERENCES `userpermissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table userpermissions_users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userpermissions_users`;

CREATE TABLE `userpermissions_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `permissionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userpermissions_users_permissionId_userId_unq_idx` (`permissionId`,`userId`),
  KEY `userpermissions_users_userId_idx` (`userId`),
  CONSTRAINT `userpermissions_users_permissionId_fk` FOREIGN KEY (`permissionId`) REFERENCES `userpermissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `userpermissions_users_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table userpreferences
# ------------------------------------------------------------

DROP TABLE IF EXISTS `userpreferences`;

CREATE TABLE `userpreferences` (
  `userId` int(11) NOT NULL AUTO_INCREMENT,
  `preferences` text,
  PRIMARY KEY (`userId`),
  CONSTRAINT `userpreferences_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `userpreferences` WRITE;
/*!40000 ALTER TABLE `userpreferences` DISABLE KEYS */;

INSERT INTO `userpreferences` (`userId`, `preferences`)
VALUES
	(1,'{\"language\":\"en-AU\"}');

/*!40000 ALTER TABLE `userpreferences` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `photoId` int(11) DEFAULT NULL,
  `firstName` varchar(100) DEFAULT NULL,
  `lastName` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `client` tinyint(1) NOT NULL DEFAULT '0',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  `suspended` tinyint(1) NOT NULL DEFAULT '0',
  `pending` tinyint(1) NOT NULL DEFAULT '0',
  `lastLoginDate` datetime DEFAULT NULL,
  `lastLoginAttemptIp` varchar(45) DEFAULT NULL,
  `invalidLoginWindowStart` datetime DEFAULT NULL,
  `invalidLoginCount` tinyint(3) unsigned DEFAULT NULL,
  `lastInvalidLoginDate` datetime DEFAULT NULL,
  `lockoutDate` datetime DEFAULT NULL,
  `verificationCode` varchar(255) DEFAULT NULL,
  `verificationCodeIssuedDate` datetime DEFAULT NULL,
  `unverifiedEmail` varchar(255) DEFAULT NULL,
  `passwordResetRequired` tinyint(1) NOT NULL DEFAULT '0',
  `lastPasswordChangeDate` datetime DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unq_idx` (`username`),
  UNIQUE KEY `users_email_unq_idx` (`email`),
  KEY `users_uid_idx` (`uid`),
  KEY `users_verificationCode_idx` (`verificationCode`),
  KEY `users_photoId_fk` (`photoId`),
  CONSTRAINT `users_id_fk` FOREIGN KEY (`id`) REFERENCES `elements` (`id`) ON DELETE CASCADE,
  CONSTRAINT `users_photoId_fk` FOREIGN KEY (`photoId`) REFERENCES `assets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `username`, `photoId`, `firstName`, `lastName`, `email`, `password`, `admin`, `client`, `locked`, `suspended`, `pending`, `lastLoginDate`, `lastLoginAttemptIp`, `invalidLoginWindowStart`, `invalidLoginCount`, `lastInvalidLoginDate`, `lockoutDate`, `verificationCode`, `verificationCodeIssuedDate`, `unverifiedEmail`, `passwordResetRequired`, `lastPasswordChangeDate`, `dateCreated`, `dateUpdated`, `uid`)
VALUES
	(1,'admin',NULL,NULL,NULL,'test@test.com','$2y$13$xg4hbdntTAk/uamJkZbxPOKaivuK5vHIKYXAah6Cu4lvWP5omPSuO',1,0,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'2018-04-03 14:25:34','2018-04-03 14:25:34','2018-04-03 14:25:34','e4b37906-064b-4a47-893d-5816f88333a7');

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table volumefolders
# ------------------------------------------------------------

DROP TABLE IF EXISTS `volumefolders`;

CREATE TABLE `volumefolders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentId` int(11) DEFAULT NULL,
  `volumeId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `volumefolders_name_parentId_volumeId_unq_idx` (`name`,`parentId`,`volumeId`),
  KEY `volumefolders_parentId_idx` (`parentId`),
  KEY `volumefolders_volumeId_idx` (`volumeId`),
  CONSTRAINT `volumefolders_parentId_fk` FOREIGN KEY (`parentId`) REFERENCES `volumefolders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `volumefolders_volumeId_fk` FOREIGN KEY (`volumeId`) REFERENCES `volumes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table volumes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `volumes`;

CREATE TABLE `volumes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldLayoutId` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `hasUrls` tinyint(1) NOT NULL DEFAULT '1',
  `url` varchar(255) DEFAULT NULL,
  `settings` text,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `volumes_name_unq_idx` (`name`),
  UNIQUE KEY `volumes_handle_unq_idx` (`handle`),
  KEY `volumes_fieldLayoutId_idx` (`fieldLayoutId`),
  CONSTRAINT `volumes_fieldLayoutId_fk` FOREIGN KEY (`fieldLayoutId`) REFERENCES `fieldlayouts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table widgets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `widgets`;

CREATE TABLE `widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `sortOrder` smallint(6) unsigned DEFAULT NULL,
  `colspan` tinyint(1) NOT NULL DEFAULT '0',
  `settings` text,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `dateCreated` datetime NOT NULL,
  `dateUpdated` datetime NOT NULL,
  `uid` char(36) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `widgets_userId_idx` (`userId`),
  CONSTRAINT `widgets_userId_fk` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
