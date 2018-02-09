<?php
require_once (dirname(__FILE__) . "/../Database/InformationClass/AccessLogInformation.php");
$accessLogInformation = new AccessLogInformation();
$accessLogInformation->IpAddress = $_SERVER['REMOTE_ADDR'];
$accessLogInformation->BrowserInformation = $_SERVER['HTTP_USER_AGENT'];
$accessLogInformation->Url = $_SERVER["REQUEST_URI"];
$accessLogInformation->HttpReferrer = $_SERVER['HTTP_REFERER'];
$accessLogInformation->AccessDate = date("Y-m-d H:i:s");
?>