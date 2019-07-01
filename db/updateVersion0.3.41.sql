ALTER TABLE `users`
ADD `deleted` int(1) DEFAULT 0 after `perms`;

ALTER TABLE `ssh`
ADD `hide` VARCHAR(6) DEFAULT "false" after `name`;