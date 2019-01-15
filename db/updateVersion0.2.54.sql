ALTER TABLE `profile_cluster`
ADD `public` int(2) DEFAULT NULL AFTER `id`;

ALTER TABLE `profile_amazon`
ADD `public` int(2) DEFAULT NULL AFTER `id`;

ALTER TABLE `profile_cluster`
MODIFY job_queue VARCHAR(25) DEFAULT NULL AFTER `job_time`;