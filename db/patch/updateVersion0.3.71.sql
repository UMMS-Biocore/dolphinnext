ALTER TABLE `project_pipeline`
ADD `onload` varchar(15) DEFAULT NULL AFTER `process_opt`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion0.3.71.sql", "1", NOW(), NOW(), "1");