CREATE TABLE `wizard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `status` varchar(10) NOT NULL,
  `data` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `deleted` int(1) DEFAULT 0, 
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `profile_cluster`
ADD `group_id` int(11) DEFAULT 0 after `owner_id`,
ADD `auto_workdir` VARCHAR(500) DEFAULT NULL after `variable`;

ALTER TABLE `profile_amazon`
ADD `group_id` int(11) DEFAULT 0 after `owner_id`,
ADD `auto_workdir` VARCHAR(500) DEFAULT NULL after `variable`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.0.1.sql", "1", NOW(), NOW(), "1");