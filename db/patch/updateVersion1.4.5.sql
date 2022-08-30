ALTER TABLE `container`
ADD `container_env` varchar(1000) DEFAULT NULL after `summary`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.4.5.sql", "1", NOW(), NOW(), "1");

