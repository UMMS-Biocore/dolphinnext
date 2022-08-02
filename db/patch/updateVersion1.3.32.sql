ALTER TABLE `project_pipeline_input`
ADD `uid` varchar(50) DEFAULT NULL after `given_name`;

ALTER TABLE `project_pipeline`
ADD `notif_email_list` varchar(1000) DEFAULT NULL after `notif_check`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.32.sql", "1", NOW(), NOW(), "1");