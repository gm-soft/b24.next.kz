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

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Внесение оплаты</h1>

            <div>
                <form id="form" class="form-horizontal" method="post" action="paymentOrder.php">

                    <input type="hidden" name="actionPerformed" value="dealSelected">
                    <input type="hidden" name="userFullName" value="<?= $curr_user["NAME"]." ".$curr_user["LAST_NAME"] ?>">

                    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/shared/hidden-inputs.php"; ?>
                    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/centerDealSelect.php"; ?>

                    <?php require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/paymentFields.php"; ?>

                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3">
                            <div class="checkbox">
                                <label><input type="checkbox" id="confirmAction" name="confirmAction" required> Подтвердить внесение оплаты</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3">
                            <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default ">Отменить</a>
                            <button type="submit" id="submit-btn" class="btn btn-primary disabled">Внести оплату</button>
                        </div>
                    </div>
                </form>
            </div>

            <div id="alert"></div>
        </div>

        <?php
        require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/postScript.php";
        ?>


        <?php

        break;

    case "dealSelectedAsync":

        if (!isset($_REQUEST["paymentValue"]) || !isset($_REQUEST["receiptDate"]) || !isset($_REQUEST["receiptNumber"])){
            $url = "https://b24.next.kz/sales/post/paymentOrder.php?".
                "authId=".$_REQUEST["authId"]."&".
                "error=Не все поля были введены";
        }

        $deal = BitrixHelper::getDeal($_REQUEST["dealSelect"], $_REQUEST["adminToken"]);
        $title = $deal["TITLE"];
        $orderId = substr($title, 0, strpos($title, " "));
        $orderId = substr($orderId, 2);

        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/waiting.php");
        ?>

        <script>
            var welcomeDiv = $('#welcomeDiv');
            var resultDiv = $('#resultDiv');

            function SendRequest(){
                var url = "https://b24.next.kz/rest/order.php";
                var payment = {
                    "paymentValue" : <?= $_REQUEST["paymentValue"] ?>,
                    "receiptDate" : <?= $_REQUEST["receiptDate"] ?>,
                    "receiptNumber" : <?= $_REQUEST["receiptNumber"] ?>
                };

                var params = {
                    "action" : "payment.add",
                    "orderId" : <?= $orderId ?>,
                    "userId" : <?= $userId ?>,
                    "userFullName" : <?= $_REQUEST["userFullName"] ?>,
                    "payment" : payment
                };

                var request = $.ajax({
                    url: url,
                    type: "POST",
                    data: params
                });

                request.done(function(response){
                    FillResult(response);

                });
            }

            function FillResult(response){
                var closeResponse = response["result"];
                var remainder = closeResponse["remainder"];
                var barItems = closeResponse["barItems"];
                var payed = closeResponse["payed"];
                var totalCost = closeResponse["totalCost"];
                var status = closeResponse["status"];

                var barItemsCount = barItems.length;
                var message = closeResponse["message"];

                var content = "<dl class='dl-horizontal'>"+
                    "<dt>Полная стоимость заказа</dt><dd>"+totalCost+"</dd>"+
                    "<dt>Оплачено</dt><dd>"+payed+"</dd>"+
                    "<dt>Остаток по оплате</dt><dd><b>"+remainder+"</b></dd>"+
                    "<dt>Статус заказа</dt><dd><i>"+status+"</i></dd>"+
                    "<dt>Доп.заказов, кол-во</dt><dd>"+barItemsCount+"</dd>"+

                    "<dt>Сделка</dt><dd><a href='https://next.bitrix24.kz/crm/deal/show/<?= $deal["ID"] ?>/' target='_blank'>Открыть сделку ID<?= $deal["ID"] ?></a></dd>"+
                    "<dt>Сообщение сервера</dt><dd>"+message+"</a></dd>"+
                    "</dl>";
                resultDiv.html(content);
                welcomeDiv.html("Результат загружен. Ознакомьтесь с ним ниже:");
            }

            $(document).ready(function(){
                SendRequest();
            });


        </script>

        <?php

        break;


    case "dealSelected":

        if (!isset($_REQUEST["paymentValue"]) || !isset($_REQUEST["receiptDate"]) || !isset($_REQUEST["receiptNumber"])){
            $url = "https://b24.next.kz/sales/post/paymentOrder.php?".
                "authId=".$_REQUEST["authId"]."&".
                "error=Не все поля были введены";
        }

        $deal = BitrixHelper::getDeal($_REQUEST["dealSelect"], $_REQUEST["adminToken"]);
        $title = $deal["TITLE"];
        $orderId = substr($title, 0, strpos($title, " "));
        $orderId = substr($orderId, 2);

        $payment = [
            "paymentValue" => $_REQUEST["paymentValue"],
            "receiptDate" => $_REQUEST["receiptDate"],
            "receiptNumber" => $_REQUEST["receiptNumber"]

        ];

        $params = [
            "action" => "payment.add",
            "orderId" => $orderId,
            "userId" => $userId,
            "userFullName" => $_REQUEST["userFullName"],
            "payment" => $payment
        ];
        $closeResponse = query("POST", "https://b24.next.kz/rest/order.php", $params);
        $closeResponse = $closeResponse["result"];

        $remainder = $closeResponse["remainder"];
        $barItems = $closeResponse["barItems"];
        $payed = $closeResponse["payed"];
        $totalCost = $closeResponse["totalCost"];

        $barItemsCount = count($barItems);
        $message = $closeResponse["message"];
        if($closeResponse["result"] == false){
            $_GET["error"] = $message;
        }
        require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/header.php");
        ?>
        <div class="container">
            <h1>Результат внесения оплаты</h1>
            <div id="toPrint">
                <h3>Заказ ID<?= $orderId?></h3>

                <?php
                require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/resultInfo.php";
                ?>


            </div>

            <div class="text-center">
                <a href="#" id="print" class="btn btn-default">Печать</a>
                <a href="https://b24.next.kz/sales/index.php?authId=<?= $_REQUEST["authId"] ?>" id="back" class="btn btn-default">В главное меню</a>
                <?php
                $url = "https://b24.next.kz/sales/post/paymentOrder.php?".
                    "authId=".$_REQUEST["authId"];
                echo "<a href=\"$url\" class=\"btn btn-default\">Внести еще одну оплату</a>";
                ?>
            </div>

        </div>
        <?php

        break;
}
?>
    <script>
        $("#dealSelect").select2();
        $('#form').submit(function(){
            $("#submit-btn").prop('disabled',true); //
            $("a").addClass('disabled');
        });

        $('#print').click(function(){
            printContent("toPrint");
        });
    </script>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/sales/shared/footer.php");
?>