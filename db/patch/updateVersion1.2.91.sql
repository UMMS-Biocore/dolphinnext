ALTER TABLE `project_pipeline` MODIFY `dmeta` MEDIUMTEXT;
ALTER TABLE `run_log` MODIFY `run_opt` MEDIUMTEXT;



INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.91.sql", "1", NOW(), NOW(), "1");