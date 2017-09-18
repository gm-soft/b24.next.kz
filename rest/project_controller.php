<?php

require_once "/var/www/b24.next.kz/Helpers/ConfigHelper.php";

$result = false;
$action = $_REQUEST["action"];

$iniFileName = "/var/www/b24.next.kz/config.ini";

$json_array = array(
    "result" => $result,
    "action" => $action
);

if (!isset($action)) {
    require_once "/var/www/b24.next.kz/rest/project_controller.html";
}
else {
    switch ($action) {

        case 'active':

            echo ConfigHelper::IsRequestsEnabled() ? "true" : "false";
            break;

        case 'disable_requests':
            $array = ConfigHelper::ReadIniFile($iniFileName);
            $array['enable_requests'] = "false";
            ConfigHelper::WriteIniFile($array, $iniFileName);

            echo ConfigHelper::IsRequestsEnabled() ? "true" : "false";
            break;

        case 'enable_requests':

            $array = ConfigHelper::ReadIniFile($iniFileName);
            $array['enable_requests'] = "true";
            ConfigHelper::WriteIniFile($array, $iniFileName);
            echo ConfigHelper::IsRequestsEnabled() ? "true" : "false";
            break;

        default:
            //throw ;
    }
}

