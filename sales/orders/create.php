<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

$_REQUEST["action"] = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$_REQUEST["authId"] = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($_REQUEST["authId"])) {
    redirect("../sales/index.php");
}

$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : ApplicationHelper::readAccessData(true);



$curr_user = BitrixHelper::getCurrentUser($_REQUEST["authId"]);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];


$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";

switch ($actionPerformed){
    case "initiated":
        $url = "https://b24.next.kz/sales/contact2.php?action=createOrder&authId=".$_REQUEST["authId"]."";
        redirect($url);
        break;

    case "contactDefined":
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        createOrder

        <?= var_export($_REQUEST, true)?>

        <?php
        break;

    case "contactSelect":
        $contact = BitrixHelper::getContact($_REQUEST["contactSelect"], $_REQUEST["adminToken"]);
        var_export($contact);
        break;

    case "contactCreate":
        var_export($_REQUEST);
        break;
    default:
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        default switch of create.php
        <?php
        break;
}

//<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
