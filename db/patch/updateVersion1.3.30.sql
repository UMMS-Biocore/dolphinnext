ALTER TABLE `biocorepipe_save`
ADD `write_group_id` varchar(50) DEFAULT NULL after `group_id`;

ALTER TABLE `process`
ADD `write_group_id` varchar(50) DEFAULT NULL after `group_id`;

ALTER TABLE `process_parameter`
ADD `write_group_id` varchar(50) DEFAULT NULL after `group_id`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.30.sql", "1", NOW(), NOW(), "1");