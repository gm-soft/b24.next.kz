<?php
	
	//require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
	require("/var/www/b24.next.kz/include/config.php");

	$query_result = query("GET", "http://b24.next.kz/rest/control.php", ["action" => "refresh"]);
	var_export($query_result);
	echo "\n";
	$query_result = $query_result && query("GET", "http://newb24.next.kz/rest/control.php", ["action" => "refresh"]);
	var_export($query_result);
	echo "\n";

	