ALTER TABLE `users`
ADD `email_notif` varchar(5) DEFAULT "true" after `role`;

ALTER TABLE `project_pipeline`
ADD `notif_check` varchar(5) DEFAULT NULL after `onload`,
ADD `email_notif` varchar(5) DEFAULT NULL after `onload`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.3.13.sql", "1", NOW(), NOW(), "1");