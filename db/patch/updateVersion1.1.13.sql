ALTER TABLE `run_log`
ADD `run_opt` text DEFAULT NULL AFTER `duration`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.1.13.sql", "1", NOW(), NOW(), "1");