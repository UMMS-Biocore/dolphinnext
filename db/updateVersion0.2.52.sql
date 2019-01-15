ALTER TABLE `users`
ADD `active` int(2) DEFAULT NULL AFTER `role`,
ADD `pass_hash` varchar(100) DEFAULT NULL AFTER `role`,
ADD `verification` varchar(45) DEFAULT NULL AFTER `role`,
DROP COLUMN clusteruser;

UPDATE users SET active = 1;

ALTER TABLE `users`
MODIFY google_id VARCHAR(100) DEFAULT NULL AFTER `lab`,
MODIFY google_image VARCHAR(255) DEFAULT NULL AFTER `photo_loc`,
MODIFY username VARCHAR(45) DEFAULT NULL AFTER `id`,
MODIFY name VARCHAR(45) DEFAULT NULL AFTER `active`;
ALTER TABLE `users`
MODIFY photo_loc VARCHAR(255) DEFAULT NULL AFTER `google_id`,
DROP INDEX userind;