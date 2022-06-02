ALTER TABLE `project_pipeline`
ADD `cron_prefix` varchar(250) DEFAULT NULL after `withReport`,
ADD `cron_min` int(10) DEFAULT NULL after `withReport`,
ADD `cron_hour` int(10) DEFAULT NULL after `withReport`,
ADD `cron_day` int(5) DEFAULT NULL after `withReport`,
ADD `cron_week` int(4) DEFAULT NULL after `withReport`,
ADD `cron_month` int(2) DEFAULT NULL after `withReport`,
ADD `cron_set_date` datetime DEFAULT NULL after `withReport`,
ADD `cron_target_date` datetime DEFAULT NULL after `withReport`,
ADD `cron_check` varchar(5) DEFAULT NULL after `withReport`;

ALTER TABLE `project_pipeline`
ADD `type` varchar(25) DEFAULT NULL after `withReport`;

ALTER TABLE `project_pipeline`
ADD `template_id` int(11) DEFAULT NULL after `type`;



ALTER TABLE `run_log`
ADD `pro_var_obj`text DEFAULT NULL after `run_opt`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.12.sql", "1", NOW(), NOW(), "1");