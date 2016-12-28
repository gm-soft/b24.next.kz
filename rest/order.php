<?php
require($_SERVER["DOCUMENT_ROOT"]."/include/config.php");

require($_SERVER["DOCUMENT_ROOT"] . "/include/help.php");
require($_SERVER["DOCUMENT_ROOT"] . "/Helpers/BitrixHelperClass.php");
require($_SERVER["DOCUMENT_ROOT"] . "/Helpers/SmsApiClass.php");
require($_SERVER["DOCUMENT_ROOT"] . "/Helpers/OrderHelperClass.php");

header('Content-Type: application/json');

$response = array(
    "result" => false,
    "message" => ""
);
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : NULL;

if (is_null($action)) {
    $response["message"] = "Действие не было передано";
    echo json_encode($response);
    die();
}
$logText = "action=".$action.". Request: ".json_encode($_REQUEST);
$processed = false;
// -------------------------------------
$adminToken = get_access_data(true);
$orderId = $_REQUEST["orderId"];
$order = OrderHelper::GetOrder($orderId);

if (is_null($order)) {

    $response["message"] = "Заказ под номером ".$orderId." не найден";
    echo json_encode($response);
    die();
}

switch ($action) {
    case "payment.add":

        $payment = $_REQUEST["payment"];
        $response["result"] = PaymentAdd($order, $payment, $adminToken);
        $processed = true;
        break;

    case "order.rent.close":
        $response["result"] = CloseOrderRent($order, $adminToken);
        $processed = true;
        break;

    case "order.cancel":
        $response["result"] = CancelOrder($order, $adminToken);
        $processed = true;
        break;
}

$logText .= $processed == true ? ". Result=true" : ". Result=false";
log_event($logText, "/log/order.php.log");
echo json_encode($response);


//-----------------------------
//-----------------------------
//-----------------------------
function PaymentAdd(Array $order, Array $payment, $adminToken) {


    $order["FinanceInfo"]["Payed"] += $payment["paymentValue"];
    $order["FinanceInfo"]["Remainder"] -= $payment["paymentValue"];

    $order["FinanceInfo"]["Payments"][] = $payment;
    $view = "";

    foreach ($order["FinanceInfo"]["Payments"] as $p){
      $view .= "[".$p["receiptDate"]."] Сумма: ".$p["paymentValue"]."\n";
    }
    $order["FinanceInfo"]["PaymentsView"] = $view;


    //----------------------------------
    $saveResult = OrderHelper::SaveOrder($order);
    $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);
    $result = [
        "saveResult" => $saveResult,
        "remainder" => $order["FinanceInfo"]["Remainder"],
        "totalCost" => $order["TotalCost"],
        "payed" => $order["FinanceInfo"]["Payed"],
        "status" => $order["Status"],
        "message" => "Оплата в размере ".$payment["paymentValue"]." успешно принята",
    ];
    $emailRes = SendPaymentEmail($order, $payment);
    return $result;
}

function CancelOrder(Array $order, $adminToken){

    $status = $order["Status"];
    $response = [
        "saveResult" => false,
        "remainder" => $order["FinanceInfo"]["Remainder"],
        "totalCost" => $order["TotalCost"],
        "payed" => $order["FinanceInfo"]["Payed"],
        "status" => $order["Status"],
        "message" => "",
    ];

    if (empty($_REQUEST["comment"])){
        $response["message"] = "Комментарий к отмене пуст. Нельзя отменить заказ без него";
        return $response;
    }

    if ($status != "Заказ подтвержден"){
        $response["message"] = "заказ не может быть отменен, так как статус заказа не позволяет это сделать";
        return $response;
    }

    $order["Status"] = "Аренда отменена";
    $order["Comment"] .= " --- Отмена заказа ".date("d-m-Y")." ---".
        "Комментарий к отмене: ".$_REQUEST["comment"];
    $saveResult = OrderHelper::SaveOrder($order);
    $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);
    $response["saveResult"] = $saveResult;

    $emailRes = SendAbandonEmail($order);
    return $response;
}

function CloseOrderRent(Array $order, $adminToken)
{
    $response = [];
    if ($_REQUEST["status"] == "dealClosed") {


        $order["Status"] = "Сделка закрыта";

        $saveResult = OrderHelper::SaveOrder($order);
        $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);

        $response = [
            "saveResult" => $updateResult && $saveResult,
            "remainder" => $order["FinanceInfo"]["Remainder"],
            "totalCost" => $order["TotalCost"],
            "barItems" => array(),
            "payed" => $order["FinanceInfo"]["Payed"],
            "status" => $order["Status"],
            "message" => "Сделка принудительно закрыта по указанию администратора",
        ];

        $emailRes = SendRentCloseEmail($order, null, true);
        return $response;

    } elseif ($order["Status"] == "Аренда проведена") {

        if ($order["FinanceInfo"]["Remainder"] <= 0.1 && $order["FinanceInfo"]["Remainder"] >= -0.1) {

            $order["Status"] = "Сделка закрыта";

            $saveResult = OrderHelper::SaveOrder($order);
            $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);

            $response = [
                "saveResult" => $updateResult && $saveResult,
                "remainder" => $order["FinanceInfo"]["Remainder"],
                "totalCost" => $order["TotalCost"],
                "barItems" => array(),
                "payed" => $order["FinanceInfo"]["Payed"],
                "status" => $order["Status"],
                "message" => "Сделка успешно закрыта",
            ];
            $emailRes = SendRentCloseEmail($order);

        } else {

            $response = [
                "saveResult" => false,
                "remainder" => $order["FinanceInfo"]["Remainder"],
                "totalCost" => $order["TotalCost"],
                "barItems" => array(),
                "payed" => $order["FinanceInfo"]["Payed"],
                "status" => $order["Status"],
                "message" => "Сделка не была закрыта из-за наличия остатка. Остаток равен " . $order["FinanceInfo"]["Remainder"],
            ];
        }

        return $response;

    } elseif ($order["Status"] == "Заказ подтвержден") {
        $barItemsData = OrderHelper::GetBarItemsByRequest($order["Id"]);
        log_debug(var_export($barItemsData, true));
        $order = $barItemsData["order"];
        $barItems = $barItemsData["barItems"];
        $barMessage = $barItemsData["message"];
        $barResult = $barItemsData["result"];


        $updateDealProductsSet = true;

        if ($order["FinanceInfo"]["Remainder"] <= 0.1 && $order["FinanceInfo"]["Remainder"] >= -0.1) {
            $order["Status"] = "Сделка закрыта";
        }

        $loyaltyCode = $order["FinanceInfo"]["LoyaltyCode"];
        if ($loyaltyCode != "") {
            //
            $senderAccount = OrderHelper::GetLoyaltyAccountByRequest($loyaltyCode);
            if ($senderAccount != null && $senderAccount["phone"] != $order["Phone"]) {
                //
                $sum = $order["TotalCost"] * $senderAccount["cashback"];
                $senderAccount["money"] += $sum;

                $senderAccount["last"] .= "[" . date("d-m-Y") . "] Кэшбек от ID" . $order["Id"] . " +" . $sum . "\n";
                $smsText = "Pozdravlyaem! Vash kod skidki ukazal klient tel:" . $order["Phone"] . ". Vy poluchili " . $sum . "t. na Vash nakopitel'nyi schet. Summa na schete " . $senderAccount["money"];
                $saveUserResult = OrderHelper::SaveLoyaltyAccountByRequest($senderAccount);
                $sendSmsResult = SmsApi::sendSms($senderAccount["phone"], $smsText);

            }
        }
        $order["Status"] = "Аренда проведена";
        if ($order["FinanceInfo"]["Remainder"] >= -0.1 && $order["FinanceInfo"]["Remainder"] <= 0.1) {
            $order["Status"] = "Сделка закрыта";
        } elseif ($_REQUEST["status"] == "dealClosed") {
            $order["Status"] = "Сделка закрыта";
        }
        $saveResult = OrderHelper::SaveOrder($order);
        $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);

        $updateProductsSet = $updateDealProductsSet == true ? OrderHelper::updateDealProductSet($order, $adminToken) : false;

        $response = [
            "saveResult" => $updateResult && $saveResult,
            "remainder" => $order["FinanceInfo"]["Remainder"],
            "totalCost" => $order["TotalCost"],
            "barItems" => $barItems,
            "payed" => $order["FinanceInfo"]["Payed"],
            "status" => $order["Status"],
            "message" => $barMessage,
        ];
        $emailRes = SendRentCloseEmail($order, $barItems);
        return $response;
    }
}


    function SendPaymentEmail(Array $order, Array $payment){
        $content = "<h2>Оплата за заказ ID".$order["Id"]."</h2>";
        $content .= "<hr>";
        $content .= "<p>Внесена оплата за заказ ID".$order["Id"].". Информация о заказе и внесенной оплате:</p>";
        $content .= "<p>";
        $content .= "<table border=0 cellspacing=0 cellpadding=5> ";
        $content .= "<tr><td>Сумма оплаты:</td> <td>".$payment["paymentValue"]."</td> </tr>";
        $content .= "<tr><td>Дата чека:</td> <td>".$payment["receiptDate"]."</td> </tr>";
        $content .= "<tr><td>Номер чека:</td> <td>".$payment["receiptNumber"]."</td> </tr>";
        $content .= "<tr><td>Принял оплату:</td> <td>".$_REQUEST["userFullName"]."</td> </tr>";
        $content .= "</table>";
        $content .= "</p>";

        $content .= "<p>".
            "Полная стоимость заказа: <b>".$order["TotalCost"]."</b><br>".
            "Оплачено за заказ: ".$order["FinanceInfo"]["Payed"]."<br>".
            "Остаток по оплате: <b>".$order["FinanceInfo"]["Remainder"]."</b><br>".
            "Статус заказа: <b>".$order["Status"]."</b><br>".
            "</p>";

        $receiver = "sales@next.kz";
        $subject = "Внесена оплата ID".$order["Id"];
        //require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/MailSmtpClass.php";

        //$result = MailSmtp::SendEmail($receiver, $subject, $content);
        $result = queryGoogleScript([
            "event" => "EmailSendRequested",
            "subject" => $subject,
            "receiver" => $receiver ,
            "content" => $content,
            "withBcc" => true,
        ]);
        return $result;
    }

    function SendAbandonEmail($order){
        $content = "<h2>Отменен заказ ID".$order["Id"]."</h2>";
        $content .= "<hr>";
        $content .= "<p>Отменен заказ ID".$order["Id"]." с комментарием ".$_REQUEST["comment"].":</p>";

        $content .= "<p>";
        $content .= "<table border=1 bordercolor=#c0c0c0 cellspacing=0 cellpadding=5>";
        $content .= "<tr><th>Опция</th> <th>Значение</th></tr>";
        $content .= "<tr><td>Мероприятие</td> <td>".$order["Event"]["Event"]." (".$order["Event"]["Zone"].")</td> </tr>";
        $content .= "<tr><td>Клиент</td> <td>".$order["ClientName"]."</td> </tr>";
        $content .= "<tr><td>Номер телефона</td> <td>".$order["Phone"]."</td> </tr>";
        $content .= "<tr><td>Центр проведения</td> <td>".$order["Center"]."</td> </tr>";
        $content .= "<tr><td>Дата мероприятия</td> <td>".$order["DateAtom"]."</td> </tr>";

        if (!is_null($order["BanquetInfo"])) {
            $content .= "<tr><td>Номер заказа фуршета</td> <td>".$order["BanquetInfo"]["BanquetId"]."</td> </tr>";
        }

        $content .= "<tr> <td>Статус заказа</td> <td>".$order["Status"]."</td> </tr>";
        $content .= "</table>";
        $content .= "<hr>";

        $content .= "<table border=0 bordercolor=#c0c0c0 cellspacing=0 cellpadding=5>";
        $content .= "<tr> <td>Оплачено за заказ</td> <td>".$order["FinanceInfo"]["Payed"]."</td> </tr>";
        $content .= "<tr> <td>Остаток по оплате</td> <td>".$order["FinanceInfo"]["Remainder"]."</td> </tr>";
        $content .= "<tr> <td>Итоговая сумма за заказ</td> <td>".$order["TotalCost"]."</td> </tr>";
        $content .= "</table>";
        $content .= "</p>";

        $content .= "<p>".
            "Комментарий к отмене: ".$_REQUEST["comment"]."<br>".
            "Менеджер: ".$_REQUEST["userFullName"]."<br>".
            "</p>";

        $receiver = "sales@next.kz";
        if (!is_null($order["BanquetInfo"])){
            $receiver .= ", coordinator@next.kz";
        }

        $subject = "Отмена заказа ID".$order["Id"];
        //require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/MailSmtpClass.php";

        //$result = MailSmtp::SendEmail($receiver, $subject, $content);
        $result = queryGoogleScript([
            "event" => "EmailSendRequested",
            "subject" => $subject,
            "receiver" => $receiver ,
            "content" => $content,
            "withBcc" => true,
        ]);
        return $result;
    }

    function SendRentCloseEmail($order, Array $barItems = null, bool $isForced = false){
        $content = "<h2>Закрыта аренда ID".$order["Id"]."</h2>";
        $content .= "<hr>";
        $content .= "<p>Закрыта аренда заказа ID".$order["Id"]." менеджером ".$_REQUEST["userFullName"]."</p>";
        if ($isForced == true) {
            $content .= "<h4>Сделка была закрыта принудительно</h4>";
        }

        $content .= "<p>";
        $content .= "<table border=1 bordercolor=#c0c0c0 cellspacing=0 cellpadding=5>";
        $content .= "<tr><th>Опция</th> <th>Значение</th></tr>";
        $content .= "<tr><td>Мероприятие</td> <td>".$order["Event"]["Event"]." (".$order["Event"]["Zone"].")</td> </tr>";
        $content .= "<tr><td>Клиент</td> <td>".$order["ClientName"]."</td> </tr>";
        $content .= "<tr><td>Номер телефона</td> <td>".$order["Phone"]."</td> </tr>";
        $content .= "<tr><td>Центр проведения</td> <td>".$order["Center"]."</td> </tr>";
        $content .= "<tr><td>Дата мероприятия</td> <td>".$order["DateAtom"]."</td> </tr>";

        if (!is_null($order["BanquetInfo"])) {
            $content .= "<tr><td>Номер заказа фуршета</td> <td>".$order["BanquetInfo"]["BanquetId"]."</td> </tr>";
        }
        $content .= "<tr> <td>Статус заказа</td> <td>".$order["Status"]."</td> </tr>";
        $content .= "</table>";
        $content .= "<hr>";

        $content .= "<table border=0 bordercolor=#c0c0c0 cellspacing=0 cellpadding=5>";
        $content .= "<tr> <td>Оплачено за заказ</td> <td>".$order["FinanceInfo"]["Payed"]."</td> </tr>";
        $content .= "<tr> <td>Остаток по оплате</td> <td>".$order["FinanceInfo"]["Remainder"]."</td> </tr>";
        $content .= "<tr> <td>Итоговая сумма за заказ</td> <td>".$order["TotalCost"]."</td> </tr>";
        $content .= "</table>";
        $content .= "</p>";

        if (!is_null($barItems) && count($barItems) > 0){
            $content .= "<p>Список доп.заказа на общую сумму <b>".$order["BanquetInfo"]["BarItemsCost"]."</b><br>";
            $content .= "<table border=1 bordercolor=#c0c0c0 cellspacing=0 cellpadding=5>";

            for ($i = 0; $i < count($barItems); $i++){

                $rowText = "<tr><td>".($i+1)."</td><td>".$barItems[$i]["name"]."</td><td>".$barItems[$i]["count"]." ".$barItems[$i]["measure"]."</td><td>".$barItems[$i]["cost"]."</td></tr>";
                $content .= $rowText;
            }
            $content .= "</table>";
        }

        $receiver = "sales@next.kz";


        $subject = "Закрыта аренда ID".$order["Id"];

        $result = queryGoogleScript([
            "event" => "EmailSendRequested",
            "subject" => $subject,
            "receiver" => $receiver ,
            "content" => $content,
            "withBcc" => true,
        ]);
        //require_once $_SERVER["DOCUMENT_ROOT"] . "/Helpers/MailSmtpClass.php";

        //$result = MailSmtp::SendEmail($receiver, $subject, $content);
        return $result;
    }