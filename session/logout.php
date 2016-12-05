<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/classes/MysqlHelperClass.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/classes/UserClass.php");

if (!isset($_COOKIE["user_id"])) redirect("../clients/index.php");

$expired = time() - 3600;
setcookie("username", "", $expired, "/");
setcookie("hash", "", $expired, "/");
setcookie("ts", "", $expired, "/");
redirect("../clients/index.php");