ALTER TABLE `profile_cluster`
ADD `bash_variable` text after `variable`;

ALTER TABLE `profile_amazon`
ADD `bash_variable` text after `variable`;

ALTER TABLE `profile_google`
ADD `bash_variable` text after `variable`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.57.sql", "1", NOW(), NOW(), "1");