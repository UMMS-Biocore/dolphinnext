ALTER TABLE `run_log` MODIFY `pro_var_obj` LONGTEXT;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.4.8.sql", "1", NOW(), NOW(), "1");