ALTER TABLE `container`
ADD `container_cmd` varchar(1000) DEFAULT NULL after `summary`,
ADD `container_port` varchar(10) DEFAULT NULL after `summary`,
ADD `container_volume` varchar(200) DEFAULT NULL after `summary`,
ADD `target_path` varchar(200) DEFAULT NULL after `summary`,
ADD `websocket_reconnection_mode` varchar(10) DEFAULT NULL after `summary`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.4.4.sql", "1", NOW(), NOW(), "1");

