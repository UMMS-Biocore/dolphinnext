ALTER TABLE `file_collection` ADD UNIQUE `unique_index`(`f_id`, `c_id`);

ALTER TABLE `users`
ADD `scope` text DEFAULT NULL AFTER `role`,
ADD `sso_id` text DEFAULT NULL AFTER `id`;

CREATE TABLE `accessTokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `accessToken` varchar(1000) NOT NULL,
  `sso_user_id` varchar(100) NOT NULL,
  `client_id` varchar(100) NOT NULL,
  `scope` text DEFAULT NULL,
  `expirationDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `refreshTokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `refreshToken` varchar(1000) NOT NULL,
  `sso_user_id` varchar(100) NOT NULL,
  `client_id` varchar(100) NOT NULL,
  `scope` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `biocorepipe_save`
ADD `publish_dmeta_dir` text DEFAULT NULL after `script_pipe_header`;

ALTER TABLE `project_pipeline`
ADD `dmeta` text DEFAULT NULL after `summary`;

INSERT INTO `update_db` (`name`, `owner_id`, `date_created`, `date_modified`, `last_modified_user`) VALUES 
("updateVersion1.2.14.sql", "1", NOW(), NOW(), "1");