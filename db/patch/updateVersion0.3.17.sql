ALTER TABLE `profile_amazon`
ADD `autoshutdown_active` varchar(6) DEFAULT NULL AFTER `name`,
ADD `autoshutdown_date` datetime DEFAULT NULL AFTER `name`,
ADD `autoshutdown_check` varchar(6) DEFAULT NULL AFTER `name`;

