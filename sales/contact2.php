<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");


$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) && !empty($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
if (is_null($_REQUEST["authId"])) {
    redirect("https://b24.next.kz/sales/index.php");
}


$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) && $_REQUEST["adminToken"] != ""  ? $_REQUEST["adminToken"] : get_access_data(true);

$curr_user = BitrixHelper::getCurrentUser($authId);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];


$displayCondition =
    $userId == "30" ||
    $userId == "1" ||
    $userId == "10" ||
    $userId == "98";

switch ($action) {
    case "booth":
        $pageRedirect = "../sales/booth.php";
        $formTitle = "Продажа аренды буса";
        break;
    case "preorder":
        $pageRedirect = "../sales/preorder.php";
        $formTitle = "Создание предзаказника";
        break;

    case "createOrder":
        $pageRedirect = "../sales/orders/create.php";
        $formTitle = "Заказ аренды для дня рождения";
        break;

    case "school":
        $pageRedirect = "../sales/school/school.php";
        $formTitle = "Продажа для школ";
        break;

    case "corporate":
        $pageRedirect = "../sales/corporate/corporate.php";
        $formTitle = "Корпоративная продажа";
        break;

}

$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
$isCorpSale = isset($_REQUEST["companyId"]) && ($action == "school" || $action == "corporate");

switch ($actionPerformed){
    case "companyDefined":
    case "initiated":
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h2><?= $formTitle ?></h2>

            <form  id="form" class="form-horizontal" method="post" action="../sales/contact2.php">
                <input type="hidden" name="action" value="<?= $_REQUEST["action"] ?>">
                <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                <input id="actionPerformed" type="hidden" name="actionPerformed" value="selectContact">
                <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                <div class="row">

                    <div class="col-sm-6">
                        <p>
                            Введите номер телефона. Будет осуществлен поиск существующего контакта по этому номеру.
                            Если номер телефона не будет найден, то будет создан новый контакт в Б24
                        </p>
                        <div class="form-group">
                            <div class="btn-group">
                                <button id="findContactSwitch" type="button" class="btn btn-primary">Найти контакт</button>
                                <button id="createContactSwitch" type="button" class="btn btn-default ">Создать контакт</button>

                            </div>
                        </div>
                    </div>

                    <?php
                    if ($isCorpSale == true){
                        $company = BitrixHelper::getCompany($_REQUEST["companyId"], $_REQUEST["adminToken"]);
                        $companyTitle = $company["TITLE"]."".$company["PHONE"][0]["VALUE"]. " [ID ".$company["ID"]."]";
                        ?>
                        <input type="hidden" name="companyId" value="<?= $_REQUEST["companyId"] ?>">

                        <div class="col-sm-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">Выбранная компания</div>
                                <div class="panel-body">
                                    <h3><?= $companyTitle ?></h3>
                                    <a href="https://next.bitrix24.kz/crm/company/show/<?= $company["ID"]?>/" target="_blank">Открыть в CRM </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>





                <div class="row">

                    <div class="col-sm-6">
                        <fieldset>
                            <legend>Поиск контакта</legend>
                            <div class='form-group'>
                                <label class='col-sm-2 control-label' for='findPhone'>Найти по номеру</label>
                                <div class='col-sm-10'>
                                    <div class='input-group'>
                                        <input type='tel' class='form-control' id='findPhone' name='contactPhone'
                                               pattern='^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$' required placeholder='Найти контакт по номеру'>
                                        <div class='input-group-btn'>
                                            <button id='searchByPhone' class='btn btn-default' type='button'>
                                                Найти
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label' for='contactSelect'>Выберите контакт</label>
                                <div class='col-sm-10'>
                                    <?php

                                    $optionText = "<option value=''>Введите номер телефона в строку поиска</option>";

                                    if ($isCorpSale) {
                                        $contacts = BitrixHelper::getContactsOfTheCompany($_REQUEST["companyId"], $_REQUEST["adminToken"]);

                                        if (count($contacts) > 0){
                                            $optionText = "";
                                            foreach ($contacts as $key => $value) {

                                                $option =
                                                    "<option value=\"".$value["ID"]."\">".$value["NAME"]." ".$value["LAST_NAME"].
                                                    " [".$value["PHONE"][0]["VALUE"]."]</option>";
                                                $optionText .= $option;
                                            }

                                        } else {
                                            $optionText = "<option value=''>Контактов в компании не найдено. Найдите другой из CRM</option>";
                                        }
                                    }
                                    ?>

                                    <select class='form-control' id='contactSelect' name='contactSelect' required <?= $isCorpSale == true && count($contacts) >= 0 ? "" : "disabled" ?> >
                                        <?= $optionText ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </div>


                    <div class="col-sm-6">
                        <fieldset>
                            <legend>Создание контакта</legend>

                            <div class='form-group'>
                                <label class='control-label col-sm-2' for='contactName'>Имя:</label>
                                <div class='col-sm-10'>
                                    <input type='text' class='form-control' id='contactName' name='contactName' required disabled>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='control-label col-sm-2' for='contactLastName'>Фамилия:</label>
                                <div class='col-sm-10'>
                                    <input type='text' class='form-control' id='contactLastName' name='contactLastName' required disabled>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='control-label col-sm-2' for='contactParent'>Родитель:</label>
                                <div class='col-sm-10'>
                                    <input type='text' class='form-control' id='contactParent' name='contactParent' required disabled>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='control-label col-sm-2' for='contactBirthday'>День рождения:</label>
                                <div class='col-sm-10'>
                                    <input type='date' class='form-control' id='contactBirthday' name='contactBirthday' required disabled>
                                </div>
                            </div>

                            <div class='form-group'>
                                <label class='col-sm-2 control-label' for='contactPhone'>Телефон</label>
                                <div class='col-sm-10'>
                                    <input type='tel' class='form-control' id='contactPhone' name='contactPhone'
                                           pattern='^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$' required placeholder='8701 111 22 33' disabled>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                </div>


                <div class="form-group">
                    <button id="sbmButton" type="submit" class="btn btn-primary">Далее</button>
                </div>
            </form>

        </div>
        <script>
            var createBtn = $('#createContactSwitch');
            var findBtn = $('#findContactSwitch');
            var sbmButton = $('#sbmButton');

            var contactSelect = $('#contactSelect');
            contactSelect.select2();

            var searchByPhoneBtn = $('#searchByPhone');


            var form = $('#form');
            var inputActionPerformed = $('#actionPerformed');

            createBtn.on("click", function(){
                //createBtn.addClass("active");
                createBtn.addClass("btn-primary");
                createBtn.removeClass("btn-default");
                ///-------------------------
                //findBtn.removeClass("active");
                findBtn.removeClass("btn-primary");
                findBtn.addClass("btn-default");

                inputActionPerformed.val("createContact");
                //-------------------------
                SetDisabledCreateInputs(false);
                SetDisabledFindInputs(true);
            });

            findBtn.on("click", function(){
               // findBtn.addClass("active");
                findBtn.removeClass("btn-default");
                findBtn.addClass("btn-primary");
                //----------
                //createBtn.removeClass("active");
                createBtn.addClass("btn-default");
                createBtn.removeClass("btn-primary");

                inputActionPerformed.val("selectContact");
                //-----------------------------
                SetDisabledCreateInputs(true);
                SetDisabledFindInputs(false);
            });


            //------------------------
            searchByPhoneBtn.on("click", function(){
                var phone = $('#findPhone').val();
                if (phone == "") return;
                LoadContactsByAjax(phone);
            });

            function SetDisabledCreateInputs(state){
                $('#contactName').prop("disabled", state);
                $('#contactLastName').prop("disabled", state);
                $('#contactParent').prop("disabled", state);
                $('#contactBirthday').prop("disabled", state);
                $('#contactPhone').prop("disabled", state);
            }

            function SetDisabledFindInputs(state){
                $('#findPhone').prop("disabled", state);
                $('#contactSelect').prop("disabled", state);
            }

            function LoadContactsByAjax(filter) {

                filter = typeof filter !== 'undefined' ? filter : null;

                var url = "https://b24.next.kz/rest/bitrix.php";
                var parameters = {
                    "action": "contacts.get",
                    "byPhone" : filter
                };

                contactSelect.prop("disabled", true);
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: parameters,
                    success: function (res) {

                        //var deals = res["result"];
                        var total = res["total"];
                        var instances = res["result"];
                        contactSelect.find('option').remove().end();

                        if (total > 0) {
                            for (var i = 0; i < instances.length; i++) {

                                var instance = instances[i];
                                var option = $("<option></option>").attr("value", instance["ID"]).text(instance["NAME"] + " " + instance["LAST_NAME"] + " (" + instance["PHONE"][0]["VALUE"] + ")");
                                contactSelect.append(option);
                            }
                            //contactSearchResult.html("<b>Контакт:</b><br><a href='https://next.bitrix24.kz/crm/contact/show/"+value+"/'>"+text+"</a>");
                        } else {
                            contactSelect.append('<option value="">Контакт в CRM не найден</option>').val('');
                        }
                        //---------------------------------
                        contactSelect.prop("disabled", false);

                    }
                });
            }

            contactSelect.change(function(){
                var value = $(this).val();
                var text =  $(this).find("option:selected").text();
                contactSearchResult.html("<b>Контакт:</b><br><a href='https://next.bitrix24.kz/crm/contact/show/"+value+"/'>"+text+"</a>");
            });


        </script>

        <?php
        break;

    case "createContact":

        $birthday = $_REQUEST["contactBirthday"];
        $birthAtom = $birthday."T12:00+03:00";
        $params = array(
            "fields[NAME]" => $_REQUEST["contactName"],
            "fields[LAST_NAME]"=> $_REQUEST["contactLastName"],
            "fields[OPENED]" => "Y",
            "fields[SOURCE_ID]" => "SELF",
            "fields[TYPE_ID]" => "CLIENT",
            "fields[BIRTHDATE]" => $birthAtom,
            "fields[UF_CRM_1468207818]" => array(0 => $_REQUEST["contactParent"]),
            "fields[PHONE][0][VALUE]" => $_REQUEST["contactPhone"],
            "fields[PHONE][0][VALUE_TYPE]" => "WORK",
            "fields[ASSIGNED_BY_ID]" => $userId,
            "auth" => $_REQUEST["adminToken"]
        );
        $createResult = BitrixHelper::callMethod("crm.contact.add", $params);
        $contactId = $createResult["result"];

        $url = "$pageRedirect?".
            "authId=$authId&".
            "action=$action&".
            "actionPerformed=contactDefined&".
            "contactId=$contactId&".
            "success=Создан новый контакт ".$_REQUEST["contactName"]." ".$_REQUEST["contactLastName"]." [$contactId]";
        if ($isCorpSale == true) {
            $url .= "&companyId=".$_REQUEST["companyId"];
        }
        redirect($url);

        break;

    case "selectContact":

        $contactId = $_REQUEST["contactSelect"];
        $contact = BitrixHelper::getContact($contactId, $_REQUEST["adminToken"]);
        $url = "$pageRedirect?".
            "authId=$authId&".
            "action=$action&".
            "actionPerformed=contactDefined&".
            "contactId=$contactId&".
            "success=Найден контакт ".$contact["NAME"]." ".$contact["LAST_NAME"]." ".$contact["PHONE"][0]["VALUE"]." [$contactId]";
        if ($isCorpSale == true) {
            $url .= "&companyId=".$_REQUEST["companyId"];
        }

        redirect($url);

        break;
}
?>
<script type="text/javascript">
    $('#form').submit(function(){
        $("#submit").prop('disabled',true);
        $('#alert').append("<strong>Внимание!</strong> Идет обрабокта информации. Не закрывайте окно!");
    });
</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
