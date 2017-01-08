<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$authId = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (!isset($_REQUEST["authId"]) || empty($_REQUEST["authId"])) {
    redirect("../sales/index.php");
}

$adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



$curr_user = BitrixHelper::getCurrentUser($authId);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];


//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
$actionPerformed = isset($_REQUEST["actionPerformed"])  ? $_REQUEST["actionPerformed"] : "initiated";
switch ($action){
    case "school":
        $actionPage = "../sales/school/school.php";
        break;

    case "closeOrder":
        $actionPage = "../sales/post/closeOrder.php";
        break;

    default:
        $actionPage = null;
        break;
}

if (is_null($actionPage)){
    $url = "../sales/index.php?".
        "authId=$authId&".
        "error=Запрашиваемое действие не найдено";
    redirect($url);
}


require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
$value = isset($_REQUEST["orderId"]) ? $_REQUEST["orderId"] : "";
?>
    <div class="container">
        <h1>Поиск заказа "Школы и лагеря"</h1>
        <div class="">
            <form id="form" class="form-horizontal" method="post" action="<?= $actionPage ?>">
                <input type="hidden" name="actionPerformed" value="school_edit">
                <input type="hidden" name="action" value="<?= $action ?>">
                <input type="hidden" name="authId" value="<?= $authId ?>">
                <input type="hidden" name="adminToken" value="<?= $adminAuthToken ?>">

                <div class="form-group">
                    <label class="control-label col-sm-3" for="orderId">Номер заказа аренды</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="orderId" name="orderId" required placeholder="Номер заказа аренды (9-1)" value="<?= $value ?>">
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
<script>
    $('#form').submit(function(){
        //$(this).find('input[type=submit]').prop('disabled', true);

        $("#submit-btn").prop('disabled',true);
        $("a").addClass('disabled');
        $('#alert').addClass("alert alert-warning").append("Идет обработка информации");
    });
</script>

<?php
    require_once($_SERVER["DOCUMENT_ROOT"]."/sales/shared/footer.php");