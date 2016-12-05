<?php

$domain = "next.bitrix24.kz";
$auth = $_REQUEST["auth"]["access_token"];
$deal_id = $_REQUEST["properties"]["dealID"];
$event_token = $_REQUEST["event_token"];

$coordinator_id = 1;
$designer_id = 1;

$banquete_file = file("include/banquet.txt");
$souvenirs_file = file("include/souvenirs.txt");
$design_file = file("include/design.txt");
$zones_file = file("include/zones.txt");