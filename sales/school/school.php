<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($authId)) {
    redirect("../sales/index.php");
}

$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



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
        $company = BitrixHelper::getCompany($companyId, $_REQUEST["adminToken"]);
        $contact = BitrixHelper::getContact($contactId, $_REQUEST["adminToken"]);
        $userFullName = $curr_user["LAST_NAME"]." ".$curr_user["NAME"];

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        $actionRequest = "school_created";


        $_REQUEST["orderId"] = "";
        $_REQUEST["userId"] = $_SESSION["user_id"];
        $_REQUEST["userFullName"] = $userFullName;

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

        $_REQUEST["companyId"] = $order["CompanyId"];
        $_REQUEST["contactId"] = $order["ContactId"];

        $_REQUEST["dealId"] = isset($order["DealId"]) ? $order["DealId"] : "";

        $company = BitrixHelper::getCompany($_REQUEST["companyId"], $_REQUEST["adminToken"]);
        $contact = BitrixHelper::getContact($_REQUEST["contactId"], $_REQUEST["adminToken"]);
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        $actionRequest = "school_edited";

        $userFullName = $curr_user["LAST_NAME"]." ".$curr_user["NAME"];
        $_REQUEST["userId"] = $_SESSION["user_id"];
        $_REQUEST["userFullName"] = $order["User"];
        ?>
        <div class="container">
            <h1>Школы и лагеря - редактирование заказа</h1>
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-form-template.php"; ?>
            <div id="alert"></div>
        </div>

        <?php
        break;

    case "order_saved":


        $url = "http://b24.next.kz/sales/calculation.php";
        $parameters = array(
            "action" => "schoolGetCost",
            "pack" => $_REQUEST["pack"],
            "packNameCode" => $_REQUEST["packNameCode"],

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

            "userId" => $_REQUEST["userId"],
            "userFullName" => $_REQUEST["userFullName"],


        );

        $response = query("POST", $url, $parameters);
        $costs = $response["result"];
        $_REQUEST["totalCost"] = $costs["totalCost"];
        $_REQUEST["totalCostDiscount"] = $costs["totalCostDiscount"];
        $_REQUEST["moneyToCash"] = $costs["moneyToCash"];
        $_REQUEST["foodCost"] = $costs["foodCost"];
        $_REQUEST["orderCost"] = $costs["orderCost"];
        $_REQUEST["packCost"] = $costs["packCost"];
        $_REQUEST["packPrice"] = $costs["packPrice"];
        $_REQUEST["transferCost"] = $costs["transferCost"];
        $_REQUEST["bribe"] = $costs["bribe"];

        $_REQUEST["packType"] = $costs["packType"];
        $_REQUEST["packName"] = $costs["packName"];
        //$_REQUEST["packNameCode"] = $costs["packNameCode"];
        $_REQUEST["centerName"] = $costs["centerName"];
        $_REQUEST["centerNameRu"] = $costs["centerNameRu"];



        $order = isset($response["order"]) ? $response["order"] : null;
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
                echo "";
            }
            ?>
        </div>
        <?php

        break;

    case "order_confirmed":

        $url = "http://b24.next.kz/sales/calculation.php";
        $actionRequest = $action == "school_created" ? "schoolCreate" : "schoolSaveChanges";
        $_REQUEST["action"] = $actionRequest;
        /*$parameters = array(
            "action" => $actionRequest,
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
            "userFullName" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
        );*/

        $response = query("POST", $url, $_REQUEST);
        $costs = $response["result"];
        log_debug(var_export($response, true));
        $order = isset($response["order"]) ? $response["order"] : null;
        $_REQUEST["dealId"] = $order["DealId"] ;
        $_REQUEST["orderId"] = $order["Id"] ;

        // $contact = BitrixHelper::getContact($_REQUEST["contactId"], $_REQUEST["adminToken"]);
        //$company = BitrixHelper::getCompany($_REQUEST["companyId"], $_REQUEST["adminToken"]);

        //$contactName = $contact["NAME"]." ".$contact["LAST_NAME"];
        //$companyTitle = $company["TITLE"];
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Школы и лагеря</h1>
            <h3>заказ сохранен</h3>

            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-result-table.php"; ?>
            <a href="#" id="print" type="button" class="btn btn-default">Печать</a>
            <a href="/sales/index.php?authId=<?= $authId ?>" id="print" type="button" class="btn btn-primary">В главное меню</a>
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