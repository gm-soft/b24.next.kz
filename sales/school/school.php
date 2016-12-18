<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($action)) {
    redirect("../sales/index.php");
}

$adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



$curr_user = BitrixHelper::getCurrentUser($authId);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

//$contacts = BitrixHelper::searchContact(BitrixHelper::formatPhone($_REQUEST["contact_phone"]), $adminAuthToken);


//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
$actionPerformed = isset($_REQUEST["actionPerformed"])  ? $_REQUEST["actionPerformed"] : "initiated";
$contactId = $_REQUEST["contactId"];
$companyId = $_REQUEST["companyId"];

switch ($actionPerformed) {
    case "initiated":
        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        $contact = BitrixHelper::getContact($contactId, $adminAuthToken);
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");


        ?>
        <div class="container">
            <h1>Школы и лагеря - создание нового заказа</h1>
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-form-template.php"; ?>
            <div id="alert"></div>
        </div>

    <?php
    break;

    case "school_edit":
        $url = "http://b24.next.kz/rest/bitrix.php";
        $params = array(
                "action" => "order.get.google",
                "id" => $_REQUEST["orderId"]
        );
        $order = query("GET", $url, $params);
        $order = isset($order["result"]) ? $order["result"] : null;



        if (is_null($order)){
            $url = "../findOrder.php?".
                "authId=$authId&".
                "action=$action&".
                "orderId=".$_REQUEST["orderId"]."&".
                "error=Заказ под номером ".$_REQUEST["orderId"]." не найден";
            redirect($url);
        }

        if ($order["Event"]["Event"] != "Школа/лагерь"){
            $url = "../findOrder.php?".
                "authId=$authId&".
                "action=$action&".
                "orderId=".$_REQUEST["orderId"]."&".
                "error=Заказ под номером ".$_REQUEST["orderId"]." не является продажой для школы/лагеря";
            redirect($url);
        }

        $companyId = $order["CompanyId"];
        $contactId = $order["ContactId"];

        $dealId = isset($order["DealId"]) ? $order["DealId"] : "";
        $orderId = $_REQUEST["orderId"];

        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        $contact = BitrixHelper::getContact($contactId, $adminAuthToken);
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");


        ?>
        <div class="container">
            <h1>Школы и лагеря - редактирование заказа</h1>
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-form-template.php"; ?>
            <div id="alert"></div>
        </div>

        <?php
        break;

    case "order_saved":
    case "order_confirmed":
    $packName = "";
    switch ($_REQUEST["pack"]) {
        case 'basePack':
            $packName = "Базовый";
            break;
        case 'standardPack':
            $packName = "Стандартный";
            break;
        case 'newYear':
            $packName = "Новогодний";
            break;
        case 'allInclusive':
            $packName = "Все включено";
            break;
    }

    switch ($_REQUEST["center"]) {
        case 'nextEse':
            $centerName = "NEXT Esentai";
            break;
        case 'nextApo':
            $centerName = "NEXT Aport";
            break;

        case 'nextPro':
            $centerName = "NEXT Promenade";
            break;
        default:
            $centerName = "Не известен";
            break;
    }

    $url = "http://b24.next.kz/sales/calculation.php";
    $parameters = array(
        "action" => $actionPerformed == "order_saved" ? "schoolGetCost" : "schoolGetCostSave",
        "pack" => $_REQUEST["pack"],
        "contactId" => $_REQUEST["contactId"],
        "companyId" => $_REQUEST["companyId"],
        "orderId" => $_REQUEST["orderId"],
        "dealId" => $_REQUEST["dealId"],

        "companyName" => $_REQUEST["companyName"],
        "status" => $_REQUEST["status"],
        "center" => $_REQUEST["center"],

        "date" => $_REQUEST["date"],
        "time" => $_REQUEST["time"],
        "duration" => $_REQUEST["duration"],

        "pupilCount" => $_REQUEST["pupilCount"],
        "pupilAge" => $_REQUEST["pupilAge"],
        "packagePrice" => $_REQUEST["packagePrice"],

        "teacherCount" => $_REQUEST["teacherCount"],
        "foodPackCount" => $_REQUEST["foodPackCount"],
        //"foodpackPrice" => $_REQUEST["foodpackPrice"],
        "transferCost" => $_REQUEST["transferCost"],
        "hasTransfer" => $_REQUEST["hasTransfer"],
        "hasFood" => $_REQUEST["hasFood"],
        "discount" => $_REQUEST["discount"],

        "discountComment" => $_REQUEST["discountComment"],
        "bribePercent" => $_REQUEST["bribePercent"],

        "contactName" => $_REQUEST["contactName"],
        "contactPhone" => $_REQUEST["contactPhone"],

        "comment" => $_REQUEST["comment"],
        "subject" => $_REQUEST["subject"],

        "userId" => $_SESSION["user_id"],
        "userFullname" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
    );

    $response = query("POST", $url, $parameters);
    $costs = $response["result"];
    $order = isset($response["order"]) ? $response["order"] : null;

    $contact = BitrixHelper::getContact($_REQUEST["contactId"], $adminAuthToken);
    $company = BitrixHelper::getCompany($_REQUEST["companyId"], $adminAuthToken);

    $contactName = $contact["NAME"]." ".$contact["LAST_NAME"];
    $companyTitle = $company["TITLE"];

    require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
    ?>
        <div class="container">
            <h1>Школы и лагеря</h1>
            <h3>Подтверждение заказа</h3>
            <?php

            require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-result-table.php";
            if ($actionPerformed == "order_saved"){

                require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-hidden-form.php";
                ?>
                <div id="alert"></div>
                <?php
            }else {
                echo "<a href=\"#\" id=\"print\" type=\"button\" class=\"btn btn-default\">Печать</a>".
                "<a href=\"/sales/index.php?authId=$authId\" id=\"print\" type=\"button\" class=\"btn btn-primary\">В главное меню</a>";
            }
            ?>
        </div>
        <?php

        break;

    }
?>
    <script>
        $('#form').submit(function(){
            //$(this).find('input[type=submit]').prop('disabled', true);

            $("#submit-btn").prop('disabled',true);
            $("a").addClass('disabled');
            $('#alert').addClass("alert alert-warning").append("Идет обработка информации");
        });

        $('#print').click(function(){
            printContent("tableToPrint");
        });
        $('#back').click(function(){
            window.history.back();
        });

        //---------------------
        /*$('#hasTransfer').change(function(){

            var html = "";
            if ($(this).val() == "yes"){

                html =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"transferCost\" name=\"transferCost\" " +
                    "required placeholder=\"Стоимость трансфера\" >";
            } else {
                html = "<input type=\"hidden\" name=\"transferCost\" value=\"0\">" +
                    "<input type=\"text\" class=\"form-control\" name=\"empty\"  value=\"Стоимость трансфера: 0\" disabled>";
            }
            $('#transfer-inputs').html(html);
        });

        $('#hasFood').change(function(){

            var html = "";
            if ($(this).val() == "yes"){

                html =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"foodPackPrice\" name=\"transferCost\" " +
                    "required placeholder=\"Стоимость одного фуд-пакета:\" >";
            } else {
                html = "<input type=\"hidden\" name=\"foodPackPrice\" value=\"0\">" +
                    "<input type=\"text\" class=\"form-control\" name=\"empty\"  value=\"Стоимость одного фуд-пакета: 0\" disabled>";
            }
            $('#food-inputs').html(html);
        });*/


    </script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/sales/shared/footer.php");

?>