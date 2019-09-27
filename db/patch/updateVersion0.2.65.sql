ALTER TABLE `project_pipeline`
ADD `deleted` int(1) DEFAULT 0 after `perms`;

ALTER TABLE `project_pipeline_input`
ADD `deleted` int(1) DEFAULT 0 after `perms`;

ALTER TABLE `run`
ADD `deleted` int(1) DEFAULT 0 after `perms`;

ALTER TABLE `project`
ADD `deleted` int(1) DEFAULT 0 after `perms`;