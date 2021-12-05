ALTER TABLE `run` ADD `date_created_last_run` datetime DEFAULT NULL;
UPDATE `run` SET `date_created_last_run` = `date_modified`;

ALTER TABLE `biocorepipe_save` ADD INDEX `owner_id_index` (`owner_id`);
ALTER TABLE `run_log` ADD INDEX `owner_id_index` (`owner_id`);
ALTER TABLE `run_log` ADD INDEX `deleted_index` (`deleted`);
ALTER TABLE `user_group` ADD INDEX `u_id_index` (`u_id`);
ALTER TABLE `user_group` ADD INDEX `g_id_index` (`g_id`);
ALTER TABLE `project_pipeline` ADD INDEX `deleted_index` (`deleted`);
ALTER TABLE `project` ADD INDEX `deleted_index` (`deleted`);
ALTER TABLE `users` ADD INDEX `deleted_index` (`deleted`);
ALTER TABLE `project_pipeline_input` ADD INDEX `deleted_index` (`deleted`);
ALTER TABLE `collection` ADD INDEX `deleted_index` (`deleted`);

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.65.sql", "1", NOW(), NOW(), "1");