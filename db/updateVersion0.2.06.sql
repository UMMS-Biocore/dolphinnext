ALTER TABLE `biocorepipe_save`
ADD `process_list` text DEFAULT NULL AFTER `nodes`,
ADD `pipeline_list` text DEFAULT NULL AFTER `nodes`;