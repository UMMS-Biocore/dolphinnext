CREATE TABLE `container` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `type` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL,
  `image_name` varchar(256) DEFAULT NULL,
  `summary` text,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `deleted` int(1) DEFAULT 0,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `biocorepipe_save`
ADD `app_list` text DEFAULT NULL after `script_pipe_header`;

CREATE TABLE `app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location` varchar(100) NOT NULL,
  `status` varchar(30) NOT NULL,
  `container_id` int(11) DEFAULT NULL,
  `cpu` int(3) DEFAULT NULL,
  `memory` int(10) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL, 
  `deleted` int(1) DEFAULT 0,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `app`
ADD `type` varchar(30) NOT NULL after `id`;

ALTER TABLE `app`
ADD `run_log_uuid` varchar(100) DEFAULT NULL after `location`;

ALTER TABLE `app`
ADD `app_uid` varchar(50) DEFAULT NULL after `location`;

ALTER TABLE `app`
ADD `dir` varchar(250) DEFAULT NULL after `location`,
ADD `filename` varchar(150) DEFAULT NULL after `location`;

ALTER TABLE `app`
ADD `port` varchar(11) DEFAULT NULL after `location`,
ADD `pid` varchar(50) DEFAULT NULL after `location`;

ALTER TABLE `app`
ADD `time` int(6) DEFAULT NULL after `cpu`;


INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.92.sql", "1", NOW(), NOW(), "1");