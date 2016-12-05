<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/bitrix_help.php");

    $action = $_REQUEST["action"];
    $user_auth = $_REQUEST["AUTH_ID"];
    $admin_auth = $_REQUEST[""];