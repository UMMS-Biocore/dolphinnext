ALTER TABLE `profile_cluster`
ADD `variable` text DEFAULT NULL after `cmd`;

ALTER TABLE `profile_amazon`
ADD `variable` text DEFAULT NULL after `cmd`;
