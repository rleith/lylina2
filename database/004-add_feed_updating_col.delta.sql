ALTER TABLE `lylina_feeds` ADD `updating` TINYINT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `lylina_feeds` CHANGE `lastmod` `lastmod` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
