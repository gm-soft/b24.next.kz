<?php
require ($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require ($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require ($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$_REQUEST["action"] = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$_REQUEST["authId"] = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;
if (is_null($_REQUEST["action"])) {
    redirect("../sales/index.php");
}



$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);
//$userId = "72";

$curr_user = BitrixHelper::getCurrentUser($_REQUEST["authId"]);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

switch ($_REQUEST["action"]) {
    case "school":
        $formTitle = "Продажа для школ";
        break;

    case "corporate":
        $pageRedirect = "../sales/corporate/corporate.php";
        $formTitle = "Корпоративная продажа";
        break;
}
$pageRedirect = "../sales/contact2.php";

$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed) {


    case "initiated":

        $companies = BitrixHelper::getCompanies($userId, $_REQUEST["adminToken"]);

        if (count($companies) == 0){
            $_GET["warning"] = array("Компаний, где Вы назначены ответственным, не найдено");
            $companies = BitrixHelper::getCompanies(null, $_REQUEST["adminToken"]);
        }
        if (count($companies) == 0) {
            // Если не найдено компанний. Нужно подумать
        }

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1><?= $formTitle ?></h1>

            <h3><?=$header ?></h3>
            <form id="form" class="form-horizontal" method="post" action="company2.php">

                <input type="hidden" name="action" value="<?= $_REQUEST["action"] ?>">
                <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                <input type="hidden" id="actionPerformed" name="actionPerformed" value="selectCompany">
                <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                <div class="form-group">
                    <div class="btn-group">
                        <button id="myCompaniesSwitch" type="button" class="btn btn-primary">Мои компании</button>
                        <button id="allCompaniesSwitch" type="button" class="btn btn-default">Все компании</button>
                        <button id="createCompanySwitch" type="button" class="btn btn-default">Создать компанию</button>

                    </div>
                </div>

                <div class="row">

                    <div class="col-sm-6">
                        <fieldset>
                            <legend>Выбор компании/школы</legend>
                            <p>Было найдено <?= count($companies) ?> компаний. Выберите нужную</p>
                            <div class="form-group">
                                <label class="control-label col-sm-2" for="companySelect">Выберите компанию:</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="companySelect" name="companySelect" required>
                                        <option value="">Выберите компанию</option>
                                        <?php
                                        $i = 0;
                                        foreach ($companies as $key => $value) {

                                            $option =
                                                "<option value='".$value["ID"]."'>".$value["TITLE"].
                                                " [ID".$value["ID"]."]</option>";
                                            echo $option;
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="col-sm-6">
                        <fieldset>
                            <legend>Создать компанию</legend>
                            <div class="form-group">
                                <label class="control-label col-sm-3" for="companyTitle">Название компании: </label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="companyTitle" name="companyTitle" required disabled>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label col-sm-3" for="companyPhone">Контактный номер телефона:</label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="tel" class="form-control" id="companyPhone" name="companyPhone"
                                               pattern="^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$" required placeholder="8(701)111-2233" disabled>
                                        <span class="input-group-addon"><i class="glyphicon glyphicon-earphone"></i></span>
                                    </div>
                                </div>
                            </div>

                        </fieldset>
                    </div>

                </div>
                <div class=" form-group">
                    <button id="submit" type="submit" class="btn btn-primary">Далее</button>
                </div>


            </form>
        </div>

        <script>
            var companySelect = $("#companySelect");
            companySelect.select2();
            var actionPerformedInput = $('#actionPerformed');

            var mySwitch = $('#myCompaniesSwitch');
            var allSwitch = $('#allCompaniesSwitch');
            var createSwitch = $('#createCompanySwitch');

            mySwitch.on("click", function(){
                mySwitch.addClass("btn-primary");
                mySwitch.removeClass("btn-default");
                ///-------------------------
                allSwitch.removeClass("btn-primary");
                allSwitch.addClass("btn-default");

                createSwitch.removeClass("btn-primary");
                createSwitch.addClass("btn-default");

                actionPerformedInput.val("selectCompany");
                //-------------------------
                SetDisabledCreateInputs(true);
                SetDisabledSelectInputs(false);

                LoadCompanies(<?= $userId ?>);
            });

            allSwitch.on("click", function(){
                allSwitch.removeClass("btn-default");
                allSwitch.addClass("btn-primary");
                //----------
                mySwitch.addClass("btn-default");
                mySwitch.removeClass("btn-primary");

                createSwitch.removeClass("btn-primary");
                createSwitch.addClass("btn-default");

                actionPerformedInput.val("selectCompany");
                //-----------------------------
                SetDisabledCreateInputs(true);
                SetDisabledSelectInputs(false);

                LoadCompanies(null);
            });

            createSwitch.on("click", function(){
                createSwitch.removeClass("btn-default");
                createSwitch.addClass("btn-primary");
                //----------
                mySwitch.addClass("btn-default");
                mySwitch.removeClass("btn-primary");

                allSwitch.removeClass("btn-primary");
                allSwitch.addClass("btn-default");

                actionPerformedInput.val("createCompany");
                //-----------------------------
                SetDisabledCreateInputs(false);
                SetDisabledSelectInputs(true);
            });


            function SetDisabledCreateInputs(state){
                $('#companyTitle').prop("disabled", state);
                $('#companyPhone').prop("disabled", state);
            }

            function SetDisabledSelectInputs(state){
                $('#companySelect').prop("disabled", state);
            }

            function LoadCompanies(filter) {

                filter = typeof filter !== 'undefined' ? filter : null;

                var url = "https://b24.next.kz/rest/bitrix.php";
                var parameters = {"action": "companies.get"};

                if (filter != null) parameters["byUser"] = filter;

                companySelect.prop("disabled", true);
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: parameters,
                    success: function (res) {
                        var total = res["total"];
                        var instances = res["result"];
                        //console.log(instances);
                        companySelect.find('option').remove().end();

                        if (total > 0) {
                            for (var i = 0; i < instances.length; i++) {

                                var instance = instances[i];
                                var title = instance["TITLE"];
                                var phone = typeof instance["PHONE"] !== 'undefined' ? " (" + instance["PHONE"][0]["VALUE"] + ")" : "";
                                var id = instance["ID"];
                                var option = $("<option></option>").attr("value", id).text(title + phone + " [ID"+id+"]");
                                companySelect.append(option);
                            }
                        } else {
                            companySelect.append('<option value="">Компании в CRM не найдены</option>').val('');
                        }
                        //---------------------------------
                        companySelect.prop("disabled", false);

                    }
                });
            }

        </script>

        <?php
        break;

    case "createCompany":
        $params = array(
            "fields[TITLE]" => $_REQUEST["companyTitle"],
            "fields[COMPANY_TYPE]"=> "CUSTOMER",
            "fields[OPENED]" => "Y",
            "fields[ASSIGNED_BY_ID]" => $userId,
            "fields[PHONE]" => $_REQUEST["companyPhone"],
            "auth" => $_REQUEST["adminToken"]
        );
        $createResult = BitrixHelper::callMethod("crm.company.add", $params);
        $companyId = $createResult["result"];

        $url = "$pageRedirect?".
            "authId=".$_REQUEST["authId"]."&".
            "action=".$_REQUEST["action"]."&".
            "actionPerformed=companyDefined&".
            "companyId=".$companyId;
        redirect($url);

        break;

    case "selectCompany":
        $url = "$pageRedirect?".
            "authId=".$_REQUEST["authId"]."&".
            "action=".$_REQUEST["action"]."&".
            "actionPerformed=companyDefined&".
            "companyId=".$_REQUEST["companySelect"];
        redirect($url);

        break;

}
?>

<?php




?>


<script>

    $('#form').submit(function(){
        $("#submit").prop('disabled',true); //
        $("a").addClass('disabled');
    });
</script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
