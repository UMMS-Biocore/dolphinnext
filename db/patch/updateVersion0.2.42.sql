ALTER TABLE `process`
ADD `process_rev_uuid` varchar(100) DEFAULT NULL AFTER `process_gid`;

ALTER TABLE `biocorepipe_save`
ADD `pipeline_rev_uuid` varchar(100) DEFAULT NULL AFTER `pipeline_gid`;

CREATE TABLE `uuid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_uuid` varchar(100) DEFAULT NULL,
  `process_rev_uuid` varchar(100) DEFAULT NULL,
  `pipeline_uuid` varchar(100) DEFAULT NULL,
  `pipeline_rev_uuid` varchar(100) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
