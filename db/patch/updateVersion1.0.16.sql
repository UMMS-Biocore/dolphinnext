CREATE TABLE `google_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `project_id` varchar(100) DEFAULT NULL,
  `key_name` varchar(150) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `deleted` int(1) DEFAULT 0, 
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `profile_google` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public` int(2) DEFAULT NULL,
  `name` varchar(256) NOT NULL,
  `autoshutdown_date` datetime DEFAULT NULL,
  `autoshutdown_active` varchar(6) DEFAULT NULL,
  `autoshutdown_check` varchar(6) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `ssh` varchar(256) DEFAULT NULL,
  `nodes` varchar(10) DEFAULT NULL,
  `node_status` text,
  `autoscale_check` varchar(6) DEFAULT NULL,
  `autoscale_maxIns` varchar(10) DEFAULT NULL,
  `autoscale_minIns` varchar(10) DEFAULT NULL,
  `next_path` varchar(256) DEFAULT NULL,
  `singu_cache` varchar(256) DEFAULT NULL,
  `google_cre_id` int(11) DEFAULT NULL,
  `ssh_id` int(11) DEFAULT NULL,
  `port` varchar(6) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `instance_type` varchar(256) DEFAULT NULL,
  `image_id` varchar(256) DEFAULT NULL,
  `executor` varchar(25) DEFAULT NULL,
  `job_time` varchar(25) DEFAULT NULL,
  `job_queue` varchar(25) DEFAULT NULL,
  `job_cpu` varchar(25) DEFAULT NULL,
  `job_memory` varchar(25) DEFAULT NULL,
  `job_clu_opt` text,
  `executor_job` varchar(25) DEFAULT NULL,
  `next_time` varchar(25) DEFAULT NULL,
  `next_queue` varchar(25) DEFAULT NULL,
  `next_cpu` varchar(25) DEFAULT NULL,
  `next_memory` varchar(25) DEFAULT NULL,
  `next_clu_opt` text,
  `cmd` varchar(500) DEFAULT NULL,
  `variable` text,
  `auto_workdir` varchar(500) DEFAULT NULL,
  `def_publishdir` varchar(500) DEFAULT NULL,
  `def_workdir` varchar(500) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT '0',
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `profile_amazon`
ADD `def_workdir` varchar(500) DEFAULT NULL AFTER `auto_workdir`,
ADD `def_publishdir` varchar(500) DEFAULT NULL AFTER `auto_workdir`;

ALTER TABLE `project_pipeline`
ADD `google_cre_id` int(11) DEFAULT 0 AFTER `amazon_cre_id`;

ALTER TABLE `file`
ADD `gs_archive_dir` varchar(500) DEFAULT NULL AFTER `s3_archive_dir`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.0.16.sql", "1", NOW(), NOW(), "1");

