ALTER TABLE `run_log`
ADD `run_opt` text DEFAULT NULL AFTER `duration`,
ADD `size` int(12) DEFAULT NULL AFTER `duration`,
ADD `deleted` int(1) DEFAULT 0 AFTER `perms`,
ADD `name` varchar(256) DEFAULT NULL AFTER `id`;

ALTER TABLE `users`
ADD `disk_usage` int(12) DEFAULT NULL AFTER `role`;

ALTER TABLE `project_pipeline`
ADD `new_run` int(1) DEFAULT 0 AFTER `last_run_uuid`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.1.13.sql", "1", NOW(), NOW(), "1");