<?php
$list = file("forStas.json");

foreach ($list as $key => $value) {
file_put_contents(
	"include/banquet.txt", 
	$value."\n", 
	FILE_APPEND
);
}