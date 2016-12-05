<?php
require($_SERVER['DOCUMENT_ROOT'] ."/include/smsc_api.php");

$phone_number = "7".substr(preg_replace('~\D+~','',$_REQUEST["properties"]["phoneNumber"]),-10);
$msg_text = $_REQUEST["properties"]["msgTxt"];

send_sms($phone_number, $msg_text);