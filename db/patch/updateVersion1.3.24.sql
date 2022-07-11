ALTER TABLE `project_pipeline`
ADD `template_uuid` varchar(100) DEFAULT NULL after `type`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.24.sql", "1", NOW(), NOW(), "1");