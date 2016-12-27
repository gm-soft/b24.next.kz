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
        $response["message"] = $response["result"]["saveResult"] == true ? "Оплата успешно принята" : "Возникла какая-то ошибка при сохранении заказа";
        break;

    case "order.deal.close":
        $response = CloseOrderDeal($order, $adminToken);
        break;

    case "order.rent.close":
        $response["result"] = CloseOrderRent($order, $adminToken);
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
        "status" => $order["Status"]
    ];
  return $result;

}

function CloseOrderDeal(Array $order, $adminToken) {
    $result = [
        "result" => false,
        "message" => "",
    ];

    if ($order["Status"] != "Аренда проведена") {
      $result["message"] = "Статус сделки не позволяет закрыть сделку. Текущий статус сделки: ".$order["Status"];
      return $result;
    }

    if ($order["FinanceInfo"]["Remainder"] <= 0.1 && $order["FinanceInfo"]["Remainder"] >= -0.1) {

        $order["Status"] = "Сделка закрыта";
        $result["result"] = true;
        $result["message"] = "Сделка успешно закрыта";

        $saveResult = OrderHelper::SaveOrder($order);
        $updateResult = OrderHelper::updateOrderDeal($order, $adminToken, true);

        $result["saveResult"] = $saveResult && $updateResult;

    } else {
        $result["result"] = false;
        $result["message"] = "Сделка не была закрыта из-за наличия остатка. Остаток равен ".$order["FinanceInfo"]["Remainder"];
    }

    return $result;
}

function CloseOrderRent(Array $order, $adminToken){
    //

    $order["Status"] = "Аренда проведена";
    //здесь работа с реестрами
    $barItemsData = OrderHelper::GetBarItemsByRequest($order["Id"]);
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

          $senderAccount["last"] .= "[".date("d-m-Y")."] Кэшбек от ID".$order["Id"]." +".$sum."\n";
          $smsText = "Pozdravlyaem! Vash kod skidki ukazal klient tel:".$order["Phone"].". Vy poluchili ".$sum."t. na Vash nakopitel'nyi schet. Summa na schete ".$senderAccount["money"];
          $saveUserResult = OrderHelper::SaveLoyaltyAccountByRequest($senderAccount);
          $sendSmsResult =  SmsApi::sendSms($senderAccount["phone"], $smsText);

        }
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
        "barMessage" => $barMessage,
    ];
    return $response;
}