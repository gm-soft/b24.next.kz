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
$contactId = $_REQUEST["contact_id"];
$companyId = $_REQUEST["company_id"];

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
                        <label class="control-label col-sm-3" for="order_id">Номер заказа аренды</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="order_id" name="order_id" required placeholder="Номер заказа аренды (9-1)">
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
                "id" => $_REQUEST["order_id"]
        );
        $order = query("GET", $url, $params);
        log_debug(var_export($order, true));
        $order = isset($order["result"]) ? $order["result"] : null;



        if (is_null($order)){
            $url = "../school/school.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=school_find&".
                "id=".$_REQUEST["id"]."&".
                "error=Заказ под номером ".$_REQUEST["id"]." не найден";
            redirect($url);
        }

        if ($order["Event"]["Event"] != "Школа/лагерь"){
            $url = "../school/school.php?".
                "auth_id=$auth_id&".
                "action=$action&".
                "action_performed=school_find&".
                "id=".$_REQUEST["id"]."&".
                "error=Заказ под номером ".$_REQUEST["id"]." не является продажой для школы/лагеря";
            redirect($url);
        }

        $companyId = $order["CompanyId"];
        $contactId = $order["ContactId"];

        $dealId = isset($order["DealId"]) ? $order["DealId"] : "";
        $orderId = $_REQUEST["order_id"];

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
        case 'next_ese':
            $centerName = "NEXT Esentai";
            break;
        case 'next_apo':
            $centerName = "NEXT Aport";
            break;

        case 'next_pro':
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
        "contact_id" => $_REQUEST["contact_id"],
        "company_id" => $_REQUEST["company_id"],
        "order_id" => $_REQUEST["order_id"],
        "deal_id" => $_REQUEST["deal_id"],

        "company_name" => $_REQUEST["company_name"],

        "center" => $_REQUEST["center"],

        "date" => $_REQUEST["date"],
        "time" => $_REQUEST["time"],
        "duration" => $_REQUEST["duration"],

        "pupil_count" => $_REQUEST["pupil_count"],
        "pupil_age" => $_REQUEST["pupil_age"],
        "package_price" => $_REQUEST["package_price"],

        "teacher_count" => $_REQUEST["teacher_count"],
        "foodpack_count" => $_REQUEST["foodpack_count"],
        "foodpack_price" => $_REQUEST["foodpack_price"],
        "transfer_cost" => $_REQUEST["transfer_cost"],
        "has_transfer" => $_REQUEST["has_transfer"],
        "has_food" => $_REQUEST["has_food"],
        "discount" => $_REQUEST["discount"],

        "discount_comment" => $_REQUEST["discount_comment"],
        "bribe_percent" => $_REQUEST["bribe_percent"],

        "contact_name" => $_REQUEST["contact_name"],
        "contact_phone" => $_REQUEST["contact_phone"],

        "comment" => $_REQUEST["comment"],
        "subject" => $_REQUEST["subject"],

        "user_id" => $_SESSION["user_id"],
        "user_fullname" => $curr_user["LAST_NAME"]." ".$curr_user["NAME"],
    );

    $response = query("POST", $url, $parameters);
    $costs = $response["result"];
    $order = isset($response["order"]) ? $response["order"] : null;

    $contact = BitrixHelper::getContact($_REQUEST["contact_id"], $adminAuthToken);
    $company = BitrixHelper::getCompany($_REQUEST["company_id"], $adminAuthToken);

    $contactName = $contact["NAME"]." ".$contact["LAST_NAME"];
    $companyTitle = $company["TITLE"];

    require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
    ?>
        <div class="container">
        <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h1>Школы и лагеря</h1>
            <h3>Подтверждение заказа</h3>



            <div id="tableToPrint">
                <table class="table table-striped">

                    <tr><th>Информация о сделке</th><td></td></tr>
                    <?php
                    if ($actionPerformed == "order_confirmed"){
                        echo "<tr><td>ID заказа аренды (Консолидация 9-1)</td><td><b>".$order["Id"]."<b></td></tr>";
                        echo "<tr><td>Номер сделки</td><td><a href='https://next.bitrix24.kz/crm/deal/show/".$order["DealId"]."/'>".$order["DealId"]."</a><b></td></tr>";
                        echo "<tr><td>Компания</td><td><a href='https://next.bitrix24.kz/crm/company/show/".$_REQUEST["company_id"]."/' target='_blank'>".$contactName."</a></td></tr>";
                        echo "<tr><td>Контакт</td><td><a href='https://next.bitrix24.kz/crm/contact/show/".$_REQUEST["contact_id"]."/' target='_blank'>".$companyTitle."</a></td></tr>";
                    }
                    ?>
                    <tr><td>Тема урока</td><td><?= $_REQUEST["subject"]?></td></tr>
                    <tr><td>Пакет</td><td><?= $packName?></td></tr>
                    <tr><td>Кол-во учеников</td><td><?= $_REQUEST["pupil_count"]?></td></tr>
                    <tr><td>Кол-во учителей</td><td><?= $_REQUEST["teacher_count"]?></td></tr>
                    <tr><td>Дата</td><td><?= $_REQUEST["date"]?></td></tr>
                    <tr><td>Время</td><td><?= $_REQUEST["time"]?></td></tr>
                    <tr><td>Продолжительность</td><td><?= $_REQUEST["duration"]?></td></tr>
                    <tr><td>Центр</td><td><?= $centerName?></td></tr>
                    <hr>
                    <tr><td>Комментарий к заказу</td><td><?= $_REQUEST["comment"]?></td></tr>
                    <tr><td>Стоимость пакетов</td><td><?= $costs["packCost"]?></td></tr>

                    <tr><th>Фуд-пакеты</th><td></td></tr>
                    <?php
                    if ($_REQUEST["has_food"] == "yes") {
                        echo "<tr><td>Наличие фуд-пакетов</td><td><b>Есть</b></td></tr>".
                        "<tr><td>Стоимость фуд-пакетов</td><td>".$costs["foodCost"]."</td></tr>";
                    } else {
                        echo "<tr><td>Наличие фуд-пакетов</td><td><b>Отсутствуют</b></td></tr>";
                    }
                    ?>
                    <!--tr><td>Кол-во фуд-пакетов</td><td><?= $_REQUEST["foodpack_count"]?></td></tr-->


                    <tr><th>Трансфер</th><td></td></tr>
                    <?php
                    if ($_REQUEST["has_transfer"] == "yes"){
                        echo "<tr><td>Наличие трансфера</td><td><b>Есть</b></td></tr>";
                        echo "<tr><td>Стоимость трансфера</td><td>".$costs["transferCost"]."</td></tr>";
                    } else {
                        echo "<tr><td>Наличие трансфера</td><td><b>Отсутствует</b></td></tr>";
                    }
                    ?>
                    <!--tr><td>Деньги водителю</td><td><?= $costs["driverCost"]?></td></tr>
                    <tr><td>Трансфер в кассу</td><td><?= $costs["transferToCash"]?></td></tr-->

                    <tr><th>Информация о скидке</th><td></td></tr>
                    <tr><td>Скидка</td><td><?= $_REQUEST["discount"]?></td></tr>
                    <tr><td>Комментарий к скидке</td><td><?= $_REQUEST["discount_comment"]?></td></tr>

                    <tr><th>Финансовая информация</th><td></td></tr>

                    <tr><td>Процент Х</td><td><?= $costs["bribe"]?></td></tr>
                    <tr><td>Полная стоимость заказа</td><td><?= $costs["totalCost"]?></td></tr>
                    <tr><td>Полная стоимость заказа (с учетом скидки)</td><td><?= $costs["totalCostDiscount"]?></td></tr>
                    <tr><td>Стоимость заказа (в кассу)</td><td><?= $costs["moneyToCash"]?></td></tr>
                </table>
            </div>

            <?php
            if ($actionPerformed == "order_saved"){
                ?>
                <form id="form" method="post" action="school.php">
                    <input type="hidden" name="action_performed" value="order_confirmed">
                    <input type="hidden" name="action" value="<?= $action ?>">
                    <input type="hidden" name="auth_id" value="<?= $auth_id ?>">
                    <input type="hidden" name="admin_token" value="<?= $adminAuthToken ?>">

                    <input type="hidden" name="contact_id" value="<?= $_REQUEST["contact_id"] ?>">
                    <input type="hidden" name="company_id" value="<?= $_REQUEST["company_id"]?>">

                    <input type="hidden" name="deal_id" value="<?= $_REQUEST["deal_id"] ?>">
                    <input type="hidden" name="order_id" value="<?= $_REQUEST["order_id"] ?>">

                    <input type="hidden" name="contact_name" value="<?= $_REQUEST["contact_name"] ?>">
                    <input type="hidden" name="contact_phone" value="<?= $_REQUEST["contact_phone"]?>">
                    <input type="hidden" name="company_name" value="<?= $_REQUEST["company_name"]?>">

                    <input type="hidden" name="pack" value="<?= $_REQUEST["pack"]?>">

                    <input type="hidden" name="pupil_count" value="<?= $_REQUEST["pupil_count"]?>">
                    <input type="hidden" name="teacher_count" value="<?= $_REQUEST["teacher_count"]?>">
                    <input type="hidden" name="center" value="<?= $_REQUEST["center"]?>">

                    <input type="hidden" name="pupil_age" value="<?= $_REQUEST["pupil_age"]?>">
                    <input type="hidden" name="package_price" value="<?= $_REQUEST["package_price"]?>">
                    <input type="hidden" name="has_transfer" value="<?= $_REQUEST["has_transfer"]?>">
                    <input type="hidden" name="has_food" value="<?= $_REQUEST["has_food"]?>">

                    <input type="hidden" name="date" value="<?= $_REQUEST["date"]?>">
                    <input type="hidden" name="time" value="<?= $_REQUEST["time"]?>">
                    <input type="hidden" name="duration" value="<?= $_REQUEST["duration"]?>">

                    <input type="hidden" name="foodpack_count" value="<?= $_REQUEST["foodpack_count"]?>">
                    <input type="hidden" name="transfer_cost" value="<?= $_REQUEST["transfer_cost"]?>">
                    <!--input type="hidden" name="driver_cost" value="<?= $_REQUEST["driver_cost"]?>"-->


                    <input type="hidden" name="bribe_percent" value="<?= $_REQUEST["bribe_percent"]?>">
                    <input type="hidden" name="discount" value="<?= $_REQUEST["discount"]?>">
                    <input type="hidden" name="discount_comment" value="<?= $_REQUEST["discount_comment"]?>">
                    <input type="hidden" name="comment" value="<?= $_REQUEST["comment"]?>">
                    <input type="hidden" name="subject" value="<?= $_REQUEST["subject"]?>">
                    <input type="hidden" name="user_id" value="<?= $userId?>">

                    <div class="form-group">
                        <a href="#" id="back" class="btn btn-default">Вернуться</a>
                        <button type="submit" id="submit-btn" class="btn btn-primary">Сохранить заказ</button>
                    </div>
                </form>
                <div id="alert"></div>
                <?php
            }
            ?>
        </div>
        <?php
        if ($actionPerformed == "order_confirmed"){
            ?>
            <div class="form-group">
                <a href="#" id="print" type="button" class="btn btn-default">Печать</a>
                <a href="/index.php?auth_id=<?= $auth_id ?>" id="print" type="button" class="btn btn-primary">В главное меню</a>
            </div>

        <?php } ?>



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
        $('#has_transfer').change(function(){

            var html = "";
            if ($(this).val() == "yes"){

                html =
                    "<input type=\"number\" step=\"1\" min=\"0\" class=\"form-control\" id=\"transfer_cost\" name=\"transfer_cost\" " +
                    "required placeholder=\"Стоимость трансфера\" >";
            } else {
                html = "<input type=\"hidden\" name=\"transfer_cost\" value=\"0\">" +
                    "<input type=\"text\" class=\"form-control\" name=\"empty\"  value=\"Стоимость трансфера: 0\" disabled>";
            }
            $('#transfer-inputs').html(html);
        });
    </script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/sales/shared/footer.php");

?>