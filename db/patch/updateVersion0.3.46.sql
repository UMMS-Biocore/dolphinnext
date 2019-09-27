ALTER TABLE `project_pipeline_input`
ADD `checkpath` int(11) DEFAULT NULL after `given_name`,
ADD `urlzip` int(11) DEFAULT NULL after `given_name`,
ADD `url` int(11) DEFAULT NULL after `given_name`;

