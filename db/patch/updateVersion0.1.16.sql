ALTER TABLE `biocorepipe_save`
ADD `script_pipe_header` text DEFAULT NULL AFTER `name`,
ADD `script_mode_header` varchar(20) DEFAULT NULL AFTER `name`,
ADD `script_pipe_footer` text DEFAULT NULL AFTER `name`,
ADD `script_mode_footer` varchar(20) DEFAULT NULL AFTER `name`;
ALTER TABLE `biocorepipe_save` DROP COLUMN `script_header`;
ALTER TABLE `biocorepipe_save` DROP COLUMN `script_mode`;

ALTER TABLE `process`
ADD `script_footer` text DEFAULT NULL AFTER `script_header`;