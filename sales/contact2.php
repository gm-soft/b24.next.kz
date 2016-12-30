<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");


$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) && !empty($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
if (is_null($_REQUEST["authId"])) {
    redirect("https://b24.next.kz/sales/index.php");
}


$adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);

$curr_user = BitrixHelper::getCurrentUser($authId);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];




switch ($action) {
    case "booth":
        $formAction = "booth.php";
        $formTitle = "Продажа аренды буса";
        break;
    case "preorder":
        $formAction = "preorder.php";
        $formTitle = "Создание предзаказника";
        break;
}

$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";

switch ($actionPerformed){
    case "initiated":
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h2><?= $formTitle ?></h2>
            <p>Введите номер телефона. Будет осуществлен поиск существующего контакта по этому номеру. Если номер телефона не будет найден, то будет создан новый контакт в Б24</p>
            <form  id="form" class="form-horizontal" method="post" action="">
                <input type="hidden" name="action" value="<?= $action ?>">
                <input type="hidden" name="authId" value="<?= $authId ?>">
                <input id="actionPerformed" type="hidden" name="actionPerformed" value="initiated">
                <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                <div class="form-group">
                    <div class="btn-group">
                        <button id="createContactSwitch" type="button" class="btn btn-default ">Создать контакт</button>
                        <button id="findContactSwitch" type="button" class="btn btn-default active">Найти контакт</button>
                    </div>
                </div>

                <div id="formInputFields">

                    <fieldset>
                        <legend>Поиск контакта</legend>
                        <div class='form-group'>
                            <label class='col-sm-2 control-label' for='contactPhone'>Найти по номеру</label>
                            <div class='col-sm-10'>
                                <div class='input-group'>
                                    <input type='tel' class='form-control' id='contactPhone' name='contactPhone'
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
                                <select class='form-control' id='contactSelect' name='contactSelect' required>
                                    <option value=''>Нет данных</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                </div>

                <div class="form-group">
                    <button id="sbmButton" type="submit" class="btn btn-primary">Далее</button>
                </div>
            </form>

        </div>
        <script>
            var createBtn = $('#createContactSwitch');
            var findBtn = $('#findContactSwitch');
            var formInputsDiv = $('#formInputFields');
            var sbmButton = $('#sbmButton');
            var pattern = '^((8|\\+7)[\- ]?)?(\\(?\\d{3}\\)?[\\- ]?)?[\\d\\- ]{7,10}$';

            var contactSelect = $('#contactSelect');
            contactSelect.select2();

            var searchByPhoneBtn = $('#searchByPhone');


            var form = $('#form');
            var inputActionPerformed = $('#actionPerformed');

            createBtn.on("click", function(){
                createBtn.addClass("active");
                findBtn.removeClass("active");
                LoadCreateFields(formInputsDiv);
            });

            findBtn.on("click", function(){
                findBtn.addClass("active");
                createBtn.removeClass("active");
                LoadFindInputs(formInputsDiv);

            });

            

            function LoadFindInputs(div, contact){
                contact = typeof contact !== 'undefined' ? contact : null;
                var content =
                    "<fieldset>"+
                        "<legend>Поиск контакта</legend>"+
                        "<div class='form-group'>"+
                            "<label class='col-sm-2 control-label' for='contactPhone'>Телефон</label>"+
                            "<div class='col-sm-10'>"+
                                "<div class='input-group'>"+
                                    "<input type='tel' class='form-control' id='contactPhone' name='contactPhone'"+
                                    " pattern='"+pattern+"' required placeholder='Найти контакт по номеру'>"+
                                    "<div class='input-group-btn'>"+
                                        "<button id='searchByPhone' class='btn btn-default' type='button'>"+
                                            "Найти"+
                                        "</button>"+
                                    "</div>"+
                                "</div>"+
                            "</div>"+
                        "</div>"+

                        "<div class='form-group'>"+
                            "<label class='col-sm-2 control-label' for='contactSelect'>Выберите контакт</label>"+
                            "<div class='col-sm-10'>"+
                                "<select class='form-control' id='contactSelect' name='contactSelect' required>";

                if (contact == null){
                    content += "<option value=''>Нет данных</option>";
                } else {
                    content += "<option value='"+contact["ID"]+"'>"+contact["NAME"]+" "+contact["LAST_NAME"]+" ("+contact["PHONE"][0]["VALUE"]+")</option>";
                }

                content +=      "</select>"+
                            "</div>"+
                        "</div>"+
                    "</fieldset>";
                div.html(content);
                sbmButton.text("Далее");
                form.attr("action", "<?= $formAction ?>");
                inputActionPerformed.val("initiated");
                searchByPhoneBtn = $('#searchByPhone');
                searchByPhoneBtn.on("click", SearchPhoneAndLoad);
            }

            function LoadCreateFields(div){
                var content =
                    "<fieldset>"+
                        "<legend>Создание контакта</legend>"+

                    "<div class='form-group'>"+
                        "<label class='control-label col-sm-2' for='contactName'>Имя:</label>"+
                        "<div class='col-sm-10'>"+
                            "<input type='text' class='form-control' id='contactName' name='contactName' required>"+
                        "</div>"+
                    "</div>"+

                    "<div class='form-group'>"+
                        "<label class='control-label col-sm-2' for='contactLastName'>Фамилия:</label>"+
                        "<div class='col-sm-10'>"+
                            "<input type='text' class='form-control' id='contactLastName' name='contactLastName' required>"+
                        "</div>"+
                    "</div>"+

                    "<div class='form-group'>"+
                        "<label class='control-label col-sm-2' for='contactParent'>Родитель:</label>"+
                        "<div class='col-sm-10'>"+
                            "<input type='text' class='form-control' id='contactParent' name='contactParent' required>"+
                        "</div>"+
                    "</div>"+

                    "<div class='form-group'>"+
                        "<label class='control-label col-sm-2' for='contactBirthday'>День рождения:</label>"+
                        "<div class='col-sm-10'>"+
                            "<input type='date' class='form-control' id='contactBirthday' name='contactBirthday' required>"+
                        "</div>"+
                    "</div>"+

                    "<div class='form-group'>"+
                        "<label class='col-sm-2 control-label' for='contactPhone'>Телефон</label>"+
                        "<div class='col-sm-10'>"+
                            "<input type='tel' class='form-control' id='contactPhone' name='contactPhone'"+
                            " pattern='"+pattern+"' required placeholder='Найти контакт по номеру'>"+
                            "</div>"+
                        "</div>"+
                    "</div>"+
                    "</fieldset>";
                div.html(content);
                sbmButton.text("Создать контакт");
                form.attr("action", "contact2.php");
                inputActionPerformed.val("contactCreate");
            }



            //------------------------
            searchByPhoneBtn.on("click", SearchPhoneAndLoad);

            var SearchPhoneAndLoad = function(){
                var phone = $('#contactPhone').val();
                if (phone == "") return;
                LoadContactsByAjax(phone);
            };

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

    case "contactCreate":

        $birthday = $_REQUEST["birthday"];
        $birthAtom = $birthday."T12:00+03:00";
        $params = array(
            "fields[NAME]" => $_REQUEST["contactName"],
            "fields[LAST_NAME]"=> $_REQUEST["contactLastName"],
            "fields[OPENED]" => "Y",
            "fields[SOURCE_ID]" => "SELF",
            "fields[TYPE_ID]" => "CLIENT",
            "fields[BIRTHDATE]" => $birthAtom,
            "fields[UF_CRM_1468207818]" => array(0 => $_REQUEST["parent"]),
            "fields[PHONE][0][VALUE]" => $_REQUEST["contactPhone"],
            "fields[PHONE][0][VALUE_TYPE]" => "WORK",
            "fields[ASSIGNED_BY_ID]" => $userId,
            "auth" => $adminAuthToken
        );
        $createResult = BitrixHelper::callMethod("crm.contact.add", $params);
        $contactId = $createResult["result"];

        $url = "../sales/$formAction?".
            "authId=$authId&".
            "action=$action&".
            "actionPerformed=contactDefined&".
            "contactId=$contactId&".
            "success=Создан новый контакт ".$_REQUEST["name"]." ".$_REQUEST["lastName"]." [$contactId]";
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
