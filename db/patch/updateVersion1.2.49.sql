RENAME TABLE `accessTokens` TO `access_tokens`;
RENAME TABLE `refreshTokens` TO `refresh_tokens`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.49.sql", "1", NOW(), NOW(), "1");