ALTER TABLE `file`
ADD `run_env` varchar(256) DEFAULT NULL AFTER `s3_archive_dir`;

CREATE TABLE `file_project` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `f_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file` (`f_id`) USING BTREE,
  KEY `project` (`p_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `file`
ADD `deleted` int(1) DEFAULT 0 after `perms`;
ALTER TABLE `file_collection`
ADD `deleted` int(1) DEFAULT 0 after `perms`;
ALTER TABLE `file_project`
ADD `deleted` int(1) DEFAULT 0 after `perms`;