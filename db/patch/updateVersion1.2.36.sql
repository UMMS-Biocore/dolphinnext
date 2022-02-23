ALTER TABLE `profile_cluster`
ADD `amazon_cre_id` int(11) DEFAULT NULL after `ssh_id`;

ALTER TABLE `profile_cluster`
ADD `def_publishdir` varchar(500) DEFAULT NULL after `auto_workdir`,
ADD `def_workdir` varchar(500) DEFAULT NULL after `auto_workdir`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.36.sql", "1", NOW(), NOW(), "1");