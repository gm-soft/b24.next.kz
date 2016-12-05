<?php
    require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/include/help.php");
    require($_SERVER["DOCUMENT_ROOT"] . "/rest/bitrix_help.php");

    header('Content-Type: application/xml');

    $response = array(
        "result" => false,
        //"request" => $_REQUEST
    );
    $action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;

    if (is_null($action)) {
        echo xml_encode($response);
        die();
    }



    echo xml_encode (null);