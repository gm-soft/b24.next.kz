<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/config.php");

$auth = $_REQUEST['auth']['access_token'];
$deal_id = $_REQUEST['data']['FIELDS']['ID'];
$formatted_txt = '';    		

$productrows = call("next.bitrix24.kz", "crm.deal.productrows.get", array(
	"id" => $deal_id,
  	"auth" => $auth)
);

$arr = objectToArray($productrows);

foreach ($arr['result'] as $key => $value) {
$formatted_txt = $formatted_txt.$arr['result'][$key]['PRODUCT_NAME']." - ".$arr['result'][$key]['QUANTITY']." ".$arr['result'][$key]['MEASURE_NAME']."\n";
}

file_put_contents(
	"ONCRMDEALUPDATE.log", 
	"Timestamp: ".date(DATE_ATOM, mktime(0, 0, 0, 7, 1, 2000))."\n"."Access token: ".$auth."\n"."Deal ID: ".$deal_id."\n"."Products list: \n".$formatted_txt."\n\n\n", 
	FILE_APPEND
);