# This is a rollup of all database schema changes applied from 3.0.0 to 3.3.x

ALTER TABLE `#__weblinks` ENGINE=InnoDB;
ALTER TABLE `#__weblinks` ADD COLUMN `version` int(10) unsigned NOT NULL DEFAULT '1';
ALTER TABLE `#__weblinks` ADD COLUMN `images` text NOT NULL;
