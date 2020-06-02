ALTER TABLE `project_pipeline`
ADD `release_date` date DEFAULT NULL AFTER `onload`;

ALTER TABLE `biocorepipe_save`
ADD `release_date` date DEFAULT NULL AFTER `publish`,
ADD `publicly_searchable` varchar(5) DEFAULT "false" AFTER `pin`;

ALTER TABLE `process`
ADD `publicly_searchable` varchar(5) DEFAULT "false" AFTER `publish`;

CREATE TABLE `token` (
  `id` int(11) NOT NULL,
  `np` int(2) NOT NULL,
  `token` varchar(10) NOT NULL,
  `date_created` datetime DEFAULT NULL,
  `owner_id` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.1.17.sql", "1", NOW(), NOW(), "1");