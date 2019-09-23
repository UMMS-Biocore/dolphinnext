ALTER TABLE `profile_amazon`
ADD `port` VARCHAR(6) DEFAULT NULL AFTER `ssh_id`,
ADD `singu_cache` VARCHAR(256) DEFAULT NULL AFTER `next_path`;

ALTER TABLE `profile_cluster`
ADD `port` VARCHAR(6) DEFAULT NULL AFTER `ssh_id`,
ADD `singu_cache` VARCHAR(256) DEFAULT NULL AFTER `next_path`;