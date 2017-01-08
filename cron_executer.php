<?php
	
	//require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
	require("/var/www/b24.next.kz/include/config.php");

	$query_result = query("GET", "https://b24.next.kz/rest/control.php", array(
		"action" => "refresh"
	));

	$query_result = $query_result && query("GET", "http://newb24.next.kz/rest/control.php", array(
		"action" => "refresh"
	));

//	echo print_r($query_result);