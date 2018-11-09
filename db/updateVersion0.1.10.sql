ALTER TABLE `input`
ADD `type` varchar(10) DEFAULT NULL AFTER `name`,
ADD `host` varchar(100) DEFAULT NULL AFTER `type`;