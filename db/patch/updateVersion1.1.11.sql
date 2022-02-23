ALTER TABLE `feedback`
ADD `owner_id` int(11) DEFAULT NULL AFTER `date_created`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.1.11.sql", "1", NOW(), NOW(), "1");