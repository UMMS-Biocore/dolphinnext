ALTER TABLE `biocorepipe_save`
ADD `release_date` date DEFAULT NULL AFTER `publish`,
ADD `publicly_searchable` varchar(5) DEFAULT "false" AFTER `pin`;

ALTER TABLE `process`
ADD `publicly_searchable` varchar(5) DEFAULT "false" AFTER `publish`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.1.17.sql", "1", NOW(), NOW(), "1");