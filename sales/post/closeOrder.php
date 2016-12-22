<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$_REQUEST["action"] = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$_REQUEST["authId"] = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($_REQUEST["authId"])) {
    redirect("../sales/index.php");
}

$adminAuthToken = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



$curr_user = BitrixHelper::getCurrentUser($_REQUEST["authId"]);
$_SESSION["user_name"] =  $curr_user["EMAIL"];
$_SESSION["user_id"] =  $curr_user["ID"];
$userId = $curr_user["ID"];

$filterFields = [
    "ASSIGNED_BY_ID",
    "STAGE_ID"
];
$filterValues = [
    //$userId,
    "24",
    "2"
];

$openDeals = BitrixHelper::getDeals($filterFields, $filterValues, $adminAuthToken);
$actionPerformed = isset($_REQUEST["actionPerformed"]) ? $_REQUEST["actionPerformed"] : "initiated";
switch ($actionPerformed){
    case "initiated":

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Закрытие аренды и подсчет доп.заказа</h1>

            <div>
                <form id="form" class="form-horizontal" method="post" action="closeOrder.php">

                    <input type="hidden" name="actionPerformed" value="initiated">
                    <input type="hidden" name="action" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="authId" value="<?= $_REQUEST["authId"] ?>">
                    <input type="hidden" name="adminToken" value="<?= $_REQUEST["adminToken"] ?>">

                    <div class="form-group">
                        <label class="control-label col-sm-3" for="dealSelect">Выберите заказ аренды</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <select class="form-control" id="dealSelect" name="dealSelect" required>
                                        <?php
                                        foreach ($openDeals as $deal){
                                            echo "<option value='".$deal["ID"]."'>".$deal["TITLE"]."</option>\n";
                                        }
                                        ?>

                                    </select>
                                    <span class="input-group-addon"><span class="glyphicon glyphicon-building"></span></span>
                                </div>
                            </div>
                    </div>

                </form>
            </div>

            <div id="alert"></div>
        </div>


        <?php

        break;

    case "dealSelected":

        break;
}
?>
<script>
    $("#dealSelect").select2();
    $('#form').submit(function(){
        $("#submit").prop('disabled',true); //
        $("a").addClass('disabled');
    });
    </script>
<?php
    require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>
