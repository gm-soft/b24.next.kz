<?php
$url = 'http://ese.ala.next.kz:14880/OnCrmDealAdd/';
$data = $_REQUEST;

// use key 'http' even if you send the request to https://...
$options = array(
    'http' => array(
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
//if ($result === FALSE) {}

file_put_contents(
	$_SERVER['DOCUMENT_ROOT'] ."log/task.log", 
	$url."/n".$result, 
	FILE_APPEND);