ALTER TABLE biocorepipe_save MODIFY script_pipe_config LONGTEXT;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion0.3.66.sql", "1", NOW(), NOW(), "1");