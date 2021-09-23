ALTER TABLE dolphinnext.biocorepipe_save CONVERT TO CHARACTER SET utf8;
ALTER TABLE dolphinnext.process CONVERT TO CHARACTER SET utf8;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.52.sql", "1", NOW(), NOW(), "1");