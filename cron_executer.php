<?php
	
	//require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
	require("/var/www/b24.next.kz/include/config.php");
	
	$executeResult = true;

	$query_result = query("GET", "http://b24.next.kz/rest/control.php", ["action" => "refresh"]);
	$executeResult = $executeResult && $query_result["result"];

	$query_result = query("GET", "http://accounts.next.kz/rest/control.php", ["action" => "releaseUsed"]);
	$executeResult = $executeResult && $query_result["result"];

	$query_result = query("GET", "http://accounts.next.kz/rest/control.php", ["action" => "updateCenters"]);
	$executeResult = $executeResult && $query_result["result"];
	
	echo $executeResult;

	