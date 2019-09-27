ALTER TABLE `process_parameter`
MODIFY COLUMN `sname` text,
MODIFY COLUMN `closure` text,
MODIFY COLUMN `reg_ex` text;

ALTER TABLE `project_pipeline`
ADD `withReport` varchar(6) DEFAULT NULL AFTER `docker_check`,
ADD `withTrace` varchar(6) DEFAULT NULL AFTER `docker_check`,
ADD `withDag` varchar(6) DEFAULT NULL AFTER `docker_check`,
ADD `withTimeline` varchar(6) DEFAULT NULL AFTER `docker_check`;

ALTER TABLE `profile_cluster`
ADD `job_clu_opt` text DEFAULT NULL AFTER `job_memory`,
ADD `next_clu_opt` text DEFAULT NULL AFTER `next_memory`;

ALTER TABLE `profile_amazon`
ADD `job_clu_opt` text DEFAULT NULL AFTER `job_memory`,
ADD `next_clu_opt` text DEFAULT NULL AFTER `next_memory`;