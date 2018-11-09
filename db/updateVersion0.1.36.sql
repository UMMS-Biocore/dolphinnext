CREATE TABLE `pipeline_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `perms` int(11) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_modified` datetime DEFAULT NULL,
  `last_modified_user` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

ALTER TABLE `biocorepipe_save`
ADD `pipeline_group_id` int(11) DEFAULT NULL AFTER `id`;

INSERT INTO `pipeline_group` (`id`, `group_name`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`) VALUES
(1, 'Public Pipelines', 1, NULL, 63, '2018-05-19 15:21:52', '2018-05-19 15:21:52', '1');

UPDATE biocorepipe_save SET pipeline_group_id = 1;