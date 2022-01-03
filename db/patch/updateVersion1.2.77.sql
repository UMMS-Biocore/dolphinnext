ALTER TABLE `process_parameter` ADD `test` varchar(2500) DEFAULT NULL after `optional`;
ALTER TABLE `process` ADD `test_env` varchar(11) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `test_work_dir` varchar(300) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `docker_check` int(2) DEFAULT 0 after `script_mode_header`;
ALTER TABLE `process` ADD `singu_check` int(2) DEFAULT 0 after `script_mode_header`;
ALTER TABLE `process` ADD `docker_img` varchar(300) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `docker_opt` varchar(300) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `singu_img` varchar(300) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `singu_opt` varchar(300) DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `script_test` text DEFAULT NULL after `script_mode_header`;
ALTER TABLE `process` ADD `script_test_mode` varchar(20) DEFAULT NULL after `script_mode_header`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.77.sql", "1", NOW(), NOW(), "1");