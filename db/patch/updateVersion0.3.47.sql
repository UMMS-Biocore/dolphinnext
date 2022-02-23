ALTER TABLE `biocorepipe_save`
ADD `script_pipe_config` text DEFAULT NULL AFTER `script_pipe_header`;

CREATE TABLE `github` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `github`
ADD `deleted` int(1) DEFAULT 0 after `perms`;

ALTER TABLE `biocorepipe_save`
ADD `github` text DEFAULT NULL AFTER `publish`;