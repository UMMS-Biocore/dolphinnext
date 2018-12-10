<?php
$secRaw = parse_ini_file(".sec", true);
$sec = $secRaw['Dolphinnext'];
define('DB', $sec['DB']);
define('DBUSER', $sec['DBUSER']);
define('DBPASS', $sec['DBPASS']);
define('DBHOST', $sec['DBHOST']);
define('DBPORT', $sec['DBPORT']);
define('SSHPATH', $sec['SSHPATH']);
define('AMAZON', $sec['AMAZON']);
define('AMZPATH', $sec['AMZPATH']);
$secConf = $secRaw['CONFIG'];
date_default_timezone_set($secConf['TIMEZONE']);
define('RUNPATH', $secConf['RUNPATH']);
define('API_PATH', $secConf['API_PATH']);
$secUsers = $secRaw['USERS'];
define('SHOW_AMAZON_KEYS', $secUsers['SHOW_AMAZON_KEYS']);
define('SHOW_SSH_KEYS', $secUsers['SHOW_SSH_KEYS']);
define('SHOW_GROUPS', $secUsers['SHOW_GROUPS']);
?>
