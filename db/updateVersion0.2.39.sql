ALTER TABLE `process`
ADD `process_uuid` varchar(100) DEFAULT NULL AFTER `process_gid`;

ALTER TABLE `biocorepipe_save`
ADD `pipeline_uuid` varchar(100) DEFAULT NULL AFTER `pipeline_gid`;

