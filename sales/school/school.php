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

//$contacts = BitrixHelper::searchContact(BitrixHelper::formatPhone($_REQUEST["contact_phone"]), $adminAuthToken);


//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
$actionPerformed = isset($_REQUEST["action_performed"])  ? $_REQUEST["action_performed"] : "initiated";
$contactId = $_REQUEST["contactId"];
$companyId = $_REQUEST["companyId"];

switch ($actionPerformed) {
    case "initiated":
        $company = BitrixHelper::getCompany($companyId, $adminAuthToken);
        $contact = BitrixHelper::getContact($contactId, $adminAuthToken);
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");


        ?>
        <div class="container">
            <h1>Школы и лагеря</h1>

            <div class="row">
                <div class="col-sm-5">

                    <div class="panel panel-primary">
                        <div class="panel-heading panel-custom"><i><?= $company["TITLE"]?></i></div>
                        <div class="panel-body">
                            Компания <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a><br>
                            Телефон: <?= $company["PHONE"][0]["VALUE"]?>
                        </div>
                    </div>

                </div>
                <div class="col-sm-5 col-md-offset-2">

                    <div class="panel panel-primary">
                        <div class="panel-heading panel-custom"><i><?= $contact["NAME"]?> <?= $contact["LAST_NAME"]?></i></div>
                        <div class="panel-body">
                            Контакт <a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a><br>
                            Телефон: <?= $contact["PHONE"][0]["VALUE"]?>
                        </div>
                    </div>

                </div>
            </div>
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-form-template.php"; ?>
            <div id="alert"></div>
        </div>

    <?php
    break;

    case "school_find":
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Поиск заказа "Школы и лагеря"</h1>
            <div class="">
                <form id="form" class="form-horizontal" method="post" action="school.php">
                    <input type="hidden" name="action_performed" value="school_edit">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                    <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="orderId">Номер заказа аренды</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="orderId" name="orderId" required placeholder="Номер заказа аренды (9-1)">
                        </div>

                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3">
                            <button type="submit" id="submit-btn"  class="btn btn-primary">Найти заказ</button>
                        </div>

                    </div>

                </form>
            </div>
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
        log_debug(var_export($order, true));
        $order = isset($order["result"]) ? $order["result"] : null;



        if (is_null($order)){
            $url = "../school/school.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=school_find&".
                "orderId=".$_REQUEST["orderId"]."&".
                "error=Заказ под номером ".$_REQUEST["orderId"]." не найден";
            redirect($url);
        }

        if ($order["Event"]["Event"] != "Школа/лагерь"){
            $url = "../school/school.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=school_find&".
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
            <h1>Школы и лагеря</h1>

            <div class="row">
                <div class="col-sm-5">

                    <div class="panel panel-primary">
                        <div class="panel-heading panel-custom"><i><?= $company["TITLE"]?></i></div>
                        <div class="panel-body">
                            Компания <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/">ID<?= $company["ID"]?></a><br>
                            Телефон: <?= $company["PHONE"][0]["VALUE"]?>
                        </div>
                    </div>

                </div>
                <div class="col-sm-5 col-md-offset-2">

                    <div class="panel panel-primary">
                        <div class="panel-heading panel-custom"><i><?= $contact["NAME"]?> <?= $contact["LAST_NAME"]?></i></div>
                        <div class="panel-body">
                            Контакт <a href="https://next.bitrix24.kz/crm/contact/show/<?= $contact["ID"]?>/">ID<?= $contact["ID"]?></a><br>
                            Телефон: <?= $contact["PHONE"][0]["VALUE"]?>
                        </div>
                    </div>

                </div>
            </div>
            <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/school/school-form-template.php"; ?>
            <div id="alert"></div>
        </div>

        <?php
        break;

    case "order_saved":
    case "order_confirmed":
    $packName = "";
    switch ($_REQUEST["pack"]) {
        case 'basepack':
            $packName = "Базовый";
            break;
        case 'standartpack':
            $packName = "Стандартный";
            break;
        case 'newyearpack':
            $packName = "Новогодний";
            break;
        case 'allinclusive':
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

        "company_name" => $_REQUEST["company_name"],
        "status" => $_REQUEST["status"],
        "center" => $_REQUEST["center"],

        "date" => $_REQUEST["date"],
        "time" => $_REQUEST["time"],
        "duration" => $_REQUEST["duration"],

        "pupilCount" => $_REQUEST["pupilCount"],
        "pupilAge" => $_REQUEST["pupilAge"],
        "packagePrice" => $_REQUEST["packagePrice"],

        "teacherCount" => $_REQUEST["teacherCount"],
        "foodpackCount" => $_REQUEST["foodpackCount"],
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
        <div class="row">
        <div class="col-md-8 col-md-offset-2">
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
                "<a href=\"/sales/index.php?auth_id=<?= $auth_id ?>\" id=\"print\" type=\"button\" class=\"btn btn-primary\">В главное меню</a>";
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
        $('#hasTransfer').change(function(){

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
    </script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/sales/shared/footer.php");

?>