ALTER TABLE `github` ADD `ssh_id` int(5) DEFAULT NULL after `token`;
ALTER TABLE `github` ADD `type` varchar(10) DEFAULT "github" after `id`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.4.9.sql", "1", NOW(), NOW(), "1");