ALTER TABLE `github`
ADD `token` varchar(256) DEFAULT NULL AFTER `password`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.13.sql", "1", NOW(), NOW(), "1");