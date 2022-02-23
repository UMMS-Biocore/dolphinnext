ALTER TABLE `run_log`
ADD `run_log_uuid` varchar(100) DEFAULT NULL after `project_pipeline_id`;

ALTER TABLE `uuid`
ADD `run_log_uuid` varchar(100) DEFAULT NULL after `id`;

ALTER TABLE `project_pipeline`
ADD `last_run_uuid` varchar(100) DEFAULT NULL after `process_opt`;

ALTER TABLE `biocorepipe_save`
ADD `publish_web_dir` text DEFAULT NULL after `script_pipe_header`;