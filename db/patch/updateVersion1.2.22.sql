ALTER TABLE `run`
ADD `main_session_uuid` varchar(50) DEFAULT NULL AFTER `pid`,
ADD `initial_session_uuid` varchar(50) DEFAULT NULL AFTER `pid`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.22.sql", "1", NOW(), NOW(), "1");