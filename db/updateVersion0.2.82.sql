CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `files_used` text DEFAULT NULL,
  `file_dir` varchar(256) DEFAULT NULL,
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


ALTER TABLE `project_pipeline_input`
ADD `collection_id` int(11) DEFAULT NULL AFTER `project_pipeline_id`;

CREATE TABLE `file_collection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `f_id` int(11) NOT NULL,
  `c_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file` (`f_id`) USING BTREE,
  KEY `collection` (`c_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `project_pipeline_input`
MODIFY input_id int(11) DEFAULT NULL AFTER `pipeline_id`;

ALTER TABLE `project_pipeline_input`
DROP foreign key project_pipeline_input_ibfk_2;

