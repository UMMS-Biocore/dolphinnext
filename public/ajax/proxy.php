<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../../config/config.php");

$url = (isset($_GET['url'])) ? $_GET['url'] : false;
if(!$url) exit;

$referer = (isset($_SERVER['HTTP_REFERER'])) ? strtolower($_SERVER['HTTP_REFERER']) : false;
$is_allowed = $referer && strpos($referer, strtolower(BASE_PATH)) !== false; //deny abuse of your proxy from outside your site

$string = ($is_allowed) ? utf8_encode(file_get_contents($url)) : 'You are not allowed to use this proxy!';
$json = json_encode($string);
$callback = (isset($_GET['callback'])) ? $_GET['callback'] : false;
if($callback){
	$jsonp = "$callback($json)";
	header('Content-Type: application/javascript');
	echo $jsonp;
	exit;
}
echo $json;