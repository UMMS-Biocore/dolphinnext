<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors', 'on');
session_start();
$_SESSION['LAST_ACTIVITY'] = time();
session_write_close();

if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
}
