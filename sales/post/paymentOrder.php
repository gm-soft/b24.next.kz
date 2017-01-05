<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");
require($_SERVER["DOCUMENT_ROOT"]."/include/help.php");
require($_SERVER["DOCUMENT_ROOT"]."/Helpers/BitrixHelperClass.php");

$_REQUEST["action"] = isset($_REQUEST["action"]) ? $_REQUEST["action"] : null;
$_REQUEST["authId"] = isset($_REQUEST["authId"]) ? $_REQUEST["authId"] : null;

if (is_null($_REQUEST["authId"])) {
    redirect("../sales/index.php");
}

$_REQUEST["adminToken"] = isset($_REQUEST["adminToken"]) ? $_REQUEST["adminToken"] : get_access_data(true);



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
                    <?php
                    require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/centerDealSelect.php";
                    ?>

                    <?php
                    require_once $_SERVER["DOCUMENT_ROOT"]."/sales/post/paymentFields.php";
                    ?>

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
        $closeResponse = query("POST", "http://b24.next.kz/rest/order.php", $params);
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