CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `files_used` text DEFAULT NULL,
  `file_dir` varchar(256) DEFAULT NULL,
  `collection_id` varchar(256) DEFAULT NULL,
  `collection_type` varchar(10) DEFAULT NULL,
  `archive_dir` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `file`
ADD `file_type` varchar(20) DEFAULT NULL AFTER `file_dir`;

CREATE TABLE `collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `file`
MODIFY collection_id int(11) DEFAULT NULL AFTER `file_dir`;

ALTER TABLE `project_pipeline_input`
ADD `collection_id` int(11) DEFAULT NULL AFTER `project_pipeline_id`;

