<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once(__DIR__."/authcode.php");

$auth=new AuthCode();
$auth->AuthCodeCallbackHandler();

?>