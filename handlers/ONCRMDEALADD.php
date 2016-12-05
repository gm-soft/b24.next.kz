
<?php
file_put_contents(
	"ONCRMDEALADD.log", 
	var_export($_REQUEST, 1)."\n", 
	FILE_APPEND
);