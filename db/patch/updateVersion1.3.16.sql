ALTER TABLE `project_pipeline`
ADD `cron_first` datetime DEFAULT NULL after `withReport`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.16.sql", "1", NOW(), NOW(), "1");