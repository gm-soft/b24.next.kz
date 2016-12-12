<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
    $auth_id = isset($_REQUEST["auth_id"]) ? $_REQUEST["auth_id"] : null;

    if (is_null($action)) {
        redirect("../sales/index.php?auth_id=".$auth_id);
    }

    $adminAuthToken = isset($_REQUEST["admin_token"]) ? $_REQUEST["admin_token"] : get_access_data(true);



    $curr_user = BitrixHelper::getCurrentUser($auth_id);
    $_SESSION["user_name"] =  $curr_user["EMAIL"];
    $_SESSION["user_id"] =  $curr_user["ID"];
    $userId = $curr_user["ID"];

    $openDeals = BitrixHelper::getDeals();
