<?php
file_put_contents(
	"log/event.log", 
	var_export($_REQUEST, 1)."\n", 
	FILE_APPEND
);
